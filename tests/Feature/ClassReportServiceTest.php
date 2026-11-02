<?php

namespace Tests\Feature;

use App\Enums\AuditEventType;
use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ClassReportService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClassReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    private function at(string $time, string $date = '2026-11-02'): Carbon
    {
        return Carbon::parse("{$date} {$time}");   // 2 Nov 2026 = Senin
    }

    private function user(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    private function mondaySchedule(): Schedule
    {
        return Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);
    }

    private function service(): ClassReportService
    {
        return app(ClassReportService::class);
    }

    public function test_fr10_piket_creates_class_report_with_all_fields(): void
    {
        $piket = $this->user(UserRole::GuruPiket);
        $schedule = $this->mondaySchedule();

        $report = $this->service()->create(
            $piket,
            $schedule,
            ClassReportStatus::KelasKosong,
            'Kelas ramai, guru belum datang',
            $this->at('07:20:00'),
        );

        $this->assertSame($piket->id, $report->reported_by);
        $this->assertSame(ClassReportStatus::KelasKosong, $report->report_status);
        $this->assertSame(FollowUpStatus::Belum, $report->follow_up_status);
        $this->assertSame('2026-11-02', $report->report_date->toDateString());
        $this->assertTrue(AuditLog::where('event_type', AuditEventType::ClassReportCreated->value)->exists());
    }

    public function test_fr10_description_is_required_except_for_guru_hadir(): void
    {
        $piket = $this->user(UserRole::GuruPiket);
        $schedule = $this->mondaySchedule();

        try {
            $this->service()->create($piket, $schedule, ClassReportStatus::KelasKosong, 'abc', $this->at('07:20:00'));
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException) {
            // diharapkan
        }

        $report = $this->service()->create($piket, $schedule, ClassReportStatus::GuruHadir, null, $this->at('07:21:00'));

        $this->assertSame(FollowUpStatus::Selesai, $report->follow_up_status);
    }

    public function test_fr10_guru_and_admin_cannot_create_reports(): void
    {
        $schedule = $this->mondaySchedule();

        foreach ([UserRole::Guru, UserRole::Administrator] as $role) {
            try {
                $this->service()->create(
                    $this->user($role),
                    $schedule,
                    ClassReportStatus::KelasKosong,
                    'Keterangan cukup',
                    $this->at('07:20:00'),
                );
                $this->fail('Seharusnya ditolak untuk '.$role->value);
            } catch (AuthorizationException) {
                // diharapkan
            }
        }

        $this->assertSame(0, ClassReport::count());
        $this->assertTrue(AuditLog::where('event_type', AuditEventType::AccessDenied->value)->exists());
    }

    public function test_fr10_future_session_and_wrong_day_are_rejected(): void
    {
        $piket = $this->user(UserRole::GuruPiket);
        $schedule = $this->mondaySchedule();

        foreach ([$this->at('06:30:00'), $this->at('07:20:00', '2026-11-03')] as $at) {
            try {
                $this->service()->create($piket, $schedule, ClassReportStatus::KelasKosong, 'Keterangan cukup', $at);
                $this->fail('Seharusnya ditolak.');
            } catch (ValidationException) {
                // diharapkan
            }
        }

        $this->assertSame(0, ClassReport::count());
    }

    public function test_fr10_follow_up_update_is_logged_with_before_and_after(): void
    {
        $piket = $this->user(UserRole::GuruPiket);
        $report = ClassReport::factory()->create(['follow_up_status' => FollowUpStatus::Belum]);

        $this->service()->updateFollowUp($piket, $report, FollowUpStatus::Proses, 'Guru pengganti dihubungi');

        $this->assertSame(FollowUpStatus::Proses, $report->fresh()->follow_up_status);

        $log = AuditLog::where('event_type', AuditEventType::ClassReportFollowUpChanged->value)->firstOrFail();
        $this->assertSame('belum', $log->before_data['follow_up_status']);
        $this->assertSame('proses', $log->after_data['follow_up_status']);
    }

    public function test_fr10_same_follow_up_status_is_rejected(): void
    {
        $piket = $this->user(UserRole::GuruPiket);
        $report = ClassReport::factory()->create(['follow_up_status' => FollowUpStatus::Belum]);

        $this->expectException(ValidationException::class);

        $this->service()->updateFollowUp($piket, $report, FollowUpStatus::Belum);
    }

    public function test_fr10_guru_cannot_update_follow_up(): void
    {
        $guru = $this->user(UserRole::Guru);
        $report = ClassReport::factory()->create(['follow_up_status' => FollowUpStatus::Belum]);

        $this->expectException(AuthorizationException::class);

        $this->service()->updateFollowUp($guru, $report, FollowUpStatus::Selesai);
    }

    public function test_nfr04_report_is_not_saved_when_audit_logging_fails(): void
    {
        $piket = $this->user(UserRole::GuruPiket);
        $schedule = $this->mondaySchedule();

        $this->mock(AuditLogger::class, fn ($mock) => $mock->shouldReceive('log')->andThrow(new RuntimeException('gagal')));

        try {
            app(ClassReportService::class)->create(
                $piket,
                $schedule,
                ClassReportStatus::KelasKosong,
                'Keterangan cukup',
                $this->at('07:20:00'),
            );
            $this->fail('Seharusnya gagal.');
        } catch (RuntimeException) {
            // diharapkan
        }

        $this->assertSame(0, ClassReport::count());
    }
}
