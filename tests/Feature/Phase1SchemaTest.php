<?php

namespace Tests\Feature;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class Phase1SchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_fr07_duplicate_attendance_for_same_session_and_date_is_blocked(): void
    {
        $schedule = Schedule::factory()->create();
        Attendance::factory()->create(['schedule_id' => $schedule->id, 'attendance_date' => '2026-11-02']);

        $this->expectException(QueryException::class);

        Attendance::factory()->create(['schedule_id' => $schedule->id, 'attendance_date' => '2026-11-02']);
    }

    public function test_same_session_on_different_dates_is_allowed(): void
    {
        $schedule = Schedule::factory()->create();
        Attendance::factory()->create(['schedule_id' => $schedule->id, 'attendance_date' => '2026-11-02']);
        Attendance::factory()->create(['schedule_id' => $schedule->id, 'attendance_date' => '2026-11-09']);

        $this->assertSame(2, Attendance::count());
    }

    public function test_fr12_audit_log_cannot_be_updated_or_deleted_via_eloquent(): void
    {
        $log = AuditLog::create([
            'event_type' => AuditEventType::LoginFailed,
            'action_result' => AuditResult::Failed,
            'occurred_at' => now(),
            'entry_hash' => str_repeat('a', 64),
        ]);

        $this->assertThrows(fn () => $log->update(['reason' => 'diubah']), \LogicException::class);
        $this->assertThrows(fn () => $log->delete(), \LogicException::class);
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $before = [User::count(), Schedule::count(), Role::count()];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($before, [User::count(), Schedule::count(), Role::count()]);
        $this->assertSame(3, Role::count());
    }

    public function test_fr03_roles_have_expected_baseline_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();
        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();

        $this->assertTrue($guru->can('attendance.record'));
        $this->assertFalse($guru->can('users.view'));
        $this->assertFalse($guru->can('audit_logs.view'));

        $this->assertTrue($piket->can('dashboard.view'));
        $this->assertFalse($piket->can('audit_logs.view'));

        $this->assertTrue($admin->can('audit_logs.view'));
        $this->assertFalse($admin->can('attendance.record'));
    }

    public function test_demo_schedule_has_no_teacher_conflicts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $clashes = Schedule::query()
            ->selectRaw('teacher_id, day_of_week, start_time, COUNT(*) as total')
            ->groupBy('teacher_id', 'day_of_week', 'start_time')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $this->assertSame(0, $clashes);
    }
}
