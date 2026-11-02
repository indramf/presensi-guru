<?php

namespace Tests\Feature;

use App\Enums\AttendanceRejection;
use App\Enums\AttendanceStatus;
use App\Enums\AuditEventType;
use App\Enums\UserRole;
use App\Exceptions\AttendanceRejectedException;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    /** 2 November 2026 adalah hari Senin. */
    private function at(string $time, string $date = '2026-11-02'): Carbon
    {
        return Carbon::parse("{$date} {$time}");
    }

    private function guruWithSchedule(): array
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Guru->value);

        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        $schedule = Schedule::factory()->create([
            'teacher_id' => $teacher->id,
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);

        return [$user, $schedule];
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    private function service(): AttendanceService
    {
        return app(AttendanceService::class);
    }

    public function test_fr06_on_time_attendance_is_recorded_as_hadir(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $attendance = $this->service()->record($user, $schedule, $this->at('07:05:00'));

        $this->assertSame(AttendanceStatus::Hadir, $attendance->status);
        $this->assertSame($user->id, $attendance->recorded_by);
        $this->assertSame('2026-11-02', $attendance->attendance_date->toDateString());
        $this->assertTrue(AuditLog::where('event_type', AuditEventType::AttendanceRecorded->value)->exists());
    }

    public function test_fr06_exactly_at_threshold_is_still_hadir(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $this->assertSame(
            AttendanceStatus::Hadir,
            $this->service()->record($user, $schedule, $this->at('07:10:00'))->status
        );
    }

    public function test_fr06_after_late_threshold_is_terlambat(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $this->assertSame(
            AttendanceStatus::Terlambat,
            $this->service()->record($user, $schedule, $this->at('07:10:01'))->status
        );
    }

    public function test_fr07_window_opens_15_minutes_before_start(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $this->service()->record($user, $schedule, $this->at('06:45:00'));

        $this->assertSame(1, Attendance::count());
    }

    public function test_fr07_before_window_is_rejected_and_logged(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        try {
            $this->service()->record($user, $schedule, $this->at('06:44:59'));
            $this->fail('Seharusnya ditolak.');
        } catch (AttendanceRejectedException $e) {
            $this->assertSame(AttendanceRejection::OutsideWindow, $e->reason);
        }

        $this->assertSame(0, Attendance::count());
        $this->assertTrue(
            AuditLog::where('event_type', AuditEventType::AttendanceRejected->value)
                ->where('reason', 'outside_window')->exists()
        );
    }

    public function test_fr07_after_session_end_is_rejected(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $this->expectException(AttendanceRejectedException::class);

        $this->service()->record($user, $schedule, $this->at('07:45:01'));
    }

    public function test_fr07_wrong_day_is_rejected(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        try {
            $this->service()->record($user, $schedule, $this->at('07:10:00', '2026-11-03')); // Selasa
            $this->fail('Seharusnya ditolak.');
        } catch (AttendanceRejectedException $e) {
            $this->assertSame(AttendanceRejection::WrongDay, $e->reason);
        }
    }

    public function test_fr07_duplicate_is_rejected_without_second_record(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $this->service()->record($user, $schedule, $this->at('07:05:00'));

        try {
            $this->service()->record($user, $schedule, $this->at('07:06:00'));
            $this->fail('Seharusnya ditolak.');
        } catch (AttendanceRejectedException $e) {
            $this->assertSame(AttendanceRejection::Duplicate, $e->reason);
        }

        $this->assertSame(1, Attendance::count());
        $this->assertTrue(
            AuditLog::where('event_type', AuditEventType::AttendanceRejected->value)
                ->where('reason', 'duplicate')->exists()
        );
    }

    public function test_fr06_teacher_cannot_record_someone_elses_schedule(): void
    {
        [, $schedule] = $this->guruWithSchedule();
        $other = $this->userWithRole(UserRole::Guru);

        try {
            $this->service()->record($other, $schedule, $this->at('07:05:00'));
            $this->fail('Seharusnya ditolak.');
        } catch (AttendanceRejectedException $e) {
            $this->assertSame(AttendanceRejection::NotYourSchedule, $e->reason);
        }

        $this->assertSame(0, Attendance::count());
        $this->assertTrue(AuditLog::where('event_type', AuditEventType::AccessDenied->value)->exists());
    }

    public function test_fr06_piket_cannot_record_attendance_for_a_teacher(): void
    {
        [, $schedule] = $this->guruWithSchedule();
        $piket = $this->userWithRole(UserRole::GuruPiket);

        $this->expectException(AttendanceRejectedException::class);

        $this->service()->record($piket, $schedule, $this->at('07:05:00'));
    }

    public function test_fr06_inactive_schedule_is_rejected(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();
        $schedule->update(['is_active' => false]);

        $this->expectException(AttendanceRejectedException::class);

        $this->service()->record($user, $schedule, $this->at('07:05:00'));
    }

    public function test_nfr04_attendance_is_not_saved_when_audit_logging_fails(): void
    {
        [$user, $schedule] = $this->guruWithSchedule();

        $this->mock(AuditLogger::class, fn ($mock) => $mock->shouldReceive('log')->andThrow(new RuntimeException('gagal')));

        try {
            app(AttendanceService::class)->record($user, $schedule, $this->at('07:05:00'));
            $this->fail('Seharusnya gagal.');
        } catch (RuntimeException) {
            // diharapkan
        }

        $this->assertSame(0, Attendance::count());
    }

    public function test_fr11_authorized_user_changes_status_with_reason(): void
    {
        $piket = $this->userWithRole(UserRole::GuruPiket);
        $attendance = Attendance::factory()->create(['status' => AttendanceStatus::Terlambat]);

        $this->service()->changeStatus($piket, $attendance, AttendanceStatus::Hadir, 'Guru sudah di kelas sejak awal');

        $this->assertSame(AttendanceStatus::Hadir, $attendance->fresh()->status);

        $log = AuditLog::where('event_type', AuditEventType::AttendanceStatusChanged->value)->firstOrFail();
        $this->assertSame('terlambat', $log->before_data['status']);
        $this->assertSame('hadir', $log->after_data['status']);
        $this->assertSame('Guru sudah di kelas sejak awal', $log->reason);
        $this->assertSame($piket->id, $log->actor_user_id);
    }

    public function test_fr11_reason_is_mandatory(): void
    {
        $piket = $this->userWithRole(UserRole::GuruPiket);
        $attendance = Attendance::factory()->create(['status' => AttendanceStatus::Terlambat]);

        $this->expectException(ValidationException::class);

        $this->service()->changeStatus($piket, $attendance, AttendanceStatus::Hadir, 'singkat');
    }

    public function test_fr11_unauthorized_user_cannot_change_status(): void
    {
        $guru = $this->userWithRole(UserRole::Guru);
        $attendance = Attendance::factory()->create(['status' => AttendanceStatus::Terlambat]);

        try {
            $this->service()->changeStatus($guru, $attendance, AttendanceStatus::Hadir, 'Alasan yang cukup panjang');
            $this->fail('Seharusnya ditolak.');
        } catch (AuthorizationException) {
            // diharapkan
        }

        $this->assertSame(AttendanceStatus::Terlambat, $attendance->fresh()->status);
        $this->assertTrue(AuditLog::where('event_type', AuditEventType::AccessDenied->value)->exists());
    }

    public function test_fr11_change_after_verification_is_flagged_in_before_data(): void
    {
        $piket = $this->userWithRole(UserRole::GuruPiket);
        $attendance = Attendance::factory()->create(['status' => AttendanceStatus::Terlambat]);

        $this->service()->verify($piket, $attendance);
        $this->service()->changeStatus($piket, $attendance->fresh(), AttendanceStatus::Hadir, 'Koreksi setelah verifikasi');

        $log = AuditLog::where('event_type', AuditEventType::AttendanceStatusChanged->value)->firstOrFail();
        $this->assertTrue($log->before_data['verified']);
    }
}
