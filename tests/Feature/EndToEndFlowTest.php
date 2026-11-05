<?php

namespace Tests\Feature;

use App\Enums\AlertStatus;
use App\Enums\AttendanceStatus;
use App\Enums\AuditEventType;
use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Enums\MonitoringStatus;
use App\Enums\UserRole;
use App\Exceptions\AttendanceRejectedException;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SecurityAlert;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\AuditIntegrityChecker;
use App\Services\AuditVerificationService;
use App\Services\ClassReportService;
use App\Services\MonitoringRow;
use App\Services\MonitoringService;
use App\Services\SecurityAlertService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    /** @return Collection<int, MonitoringRow> */
    private function rows()
    {
        return app(MonitoringService::class)->sessionsFor()->keyBy(fn ($row) => $row->schedule->id);
    }

    public function test_t06_attendance_to_alert_flow_across_all_modules(): void
    {
        $guru = $this->userWithRole(UserRole::Guru);
        $piket = $this->userWithRole(UserRole::GuruPiket);
        $admin = $this->userWithRole(UserRole::Administrator);

        $teacherA = Teacher::factory()->create(['user_id' => $guru->id]);
        $teacherB = Teacher::factory()->create();

        // 2 November 2026 adalah hari Senin. Dua sesi pada jam yang sama.
        $slot = ['day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00'];
        $sessionA = Schedule::factory()->create($slot + ['teacher_id' => $teacherA->id]);
        $sessionB = Schedule::factory()->create($slot + ['teacher_id' => $teacherB->id]);

        // 1. Guru login (audit login).
        Carbon::setTestNow('2026-11-02 07:04:00');
        event(new Login('web', $guru, false));

        // 2. Guru mengisi presensi sesi A tepat waktu.
        Carbon::setTestNow('2026-11-02 07:05:00');
        $attendance = app(AttendanceService::class)->record($guru, $sessionA);
        $this->assertSame(AttendanceStatus::Hadir, $attendance->status);

        // 3. Pengiriman kedua ditolak dan memicu AN-03.
        Carbon::setTestNow('2026-11-02 07:06:00');
        try {
            app(AttendanceService::class)->record($guru, $sessionA);
            $this->fail('Presensi ganda seharusnya ditolak.');
        } catch (AttendanceRejectedException) {
            // diharapkan
        }
        $this->assertSame(1, Attendance::count());
        $this->assertCount(1, SecurityAlert::where('rule_code', 'AN-03')->get());

        // 4. Dashboard: A hadir, B belum terkonfirmasi dan perlu tindakan.
        Carbon::setTestNow('2026-11-02 07:20:00');
        $rows = $this->rows();
        $this->assertSame(MonitoringStatus::Hadir, $rows[$sessionA->id]->status);
        $this->assertSame(MonitoringStatus::BelumTerkonfirmasi, $rows[$sessionB->id]->status);
        $this->assertTrue($rows[$sessionB->id]->needsAction);
        $this->assertSame(1, app(MonitoringService::class)->summary($rows->values())['needs_action']);

        // 5. Guru piket mencatat kondisi kelas B dan menuntaskan tindak lanjutnya.
        Carbon::setTestNow('2026-11-02 07:21:00');
        $report = app(ClassReportService::class)->create(
            $piket,
            $sessionB,
            ClassReportStatus::KelasKosong,
            'Guru belum hadir, siswa dijaga guru piket',
        );
        $this->assertTrue($this->rows()[$sessionB->id]->needsAction);   // tindak lanjut belum selesai

        app(ClassReportService::class)->updateFollowUp($piket, $report, FollowUpStatus::Proses);
        app(ClassReportService::class)->updateFollowUp($piket, $report->fresh(), FollowUpStatus::Selesai);

        $rowB = $this->rows()[$sessionB->id];
        $this->assertFalse($rowB->needsAction);
        // Tanpa presensi, sesi tidak pernah berubah menjadi "tidak hadir".
        $this->assertSame(MonitoringStatus::BelumTerkonfirmasi, $rowB->status);

        // 6. Guru piket memverifikasi presensi A lalu mengubah statusnya (AN-05).
        Carbon::setTestNow('2026-11-02 07:30:00');
        app(AttendanceService::class)->verify($piket, $attendance);
        app(AttendanceService::class)->changeStatus(
            $piket,
            $attendance->fresh(),
            AttendanceStatus::Terlambat,
            'Koreksi setelah verifikasi guru piket',
        );
        $this->assertSame(MonitoringStatus::Terlambat, $this->rows()[$sessionA->id]->status);
        $this->assertCount(1, SecurityAlert::where('rule_code', 'AN-05')->get());

        // 7. Administrator meninjau peringatan AN-05.
        $alert = SecurityAlert::where('rule_code', 'AN-05')->firstOrFail();
        $reviewed = app(SecurityAlertService::class)->changeStatus($admin, $alert, AlertStatus::Ditinjau, 'Sudah dicek');
        $this->assertSame($admin->id, $reviewed->reviewed_by);

        // 8. Administrator memeriksa integritas audit log.
        $result = app(AuditVerificationService::class)->run($admin);
        $this->assertTrue($result['ok'], implode(' | ', $result['problems']));

        // 9. Setiap modul meninggalkan jejak di audit log.
        foreach (
            [
                AuditEventType::LoginSuccess,
                AuditEventType::AttendanceRecorded,
                AuditEventType::AttendanceRejected,
                AuditEventType::ClassReportCreated,
                AuditEventType::ClassReportFollowUpChanged,
                AuditEventType::AttendanceVerified,
                AuditEventType::AttendanceStatusChanged,
                AuditEventType::AlertReviewed,
                AuditEventType::IntegrityChecked,
            ] as $type
        ) {
            $this->assertTrue(AuditLog::where('event_type', $type->value)->exists(), "Tidak ada entri {$type->value}");
        }

        // 10. Tidak ada data rahasia di log, dan rantai tetap valid sampai akhir.
        foreach (AuditLog::all() as $log) {
            $payload = strtolower(json_encode([$log->before_data, $log->after_data, $log->reason]));
            $this->assertStringNotContainsString('password', $payload);
        }

        $this->assertTrue(app(AuditIntegrityChecker::class)->verify()['ok']);
    }
}
