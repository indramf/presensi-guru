<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SecurityAlert;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_fr03_guru_cannot_manage_master_data(): void
    {
        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();

        $this->assertFalse($guru->can('viewAny', Teacher::class));
        $this->assertFalse($guru->can('create', Schedule::class));
        $this->assertFalse($guru->can('viewAny', User::class));
    }

    public function test_fr03_admin_can_manage_master_data(): void
    {
        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();

        $this->assertTrue($admin->can('viewAny', Teacher::class));
        $this->assertTrue($admin->can('create', Schedule::class));
    }

    public function test_fr15_only_admin_can_view_audit_logs_and_alerts(): void
    {
        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();
        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();

        $this->assertTrue($admin->can('viewAny', AuditLog::class));
        $this->assertFalse($piket->can('viewAny', AuditLog::class));
        $this->assertFalse($guru->can('viewAny', SecurityAlert::class));
    }

    public function test_guru_can_only_view_own_attendance(): void
    {
        $guru1 = User::where('email', 'guru1@presensi.test')->firstOrFail();
        $guru2 = User::where('email', 'guru2@presensi.test')->firstOrFail();

        $schedule = Schedule::factory()->create(['teacher_id' => $guru1->teacher->id]);
        $attendance = Attendance::factory()->create(['schedule_id' => $schedule->id]);

        $this->assertTrue($guru1->can('view', $attendance));
        $this->assertFalse($guru2->can('view', $attendance));
    }

    public function test_only_authorized_roles_can_change_attendance_status(): void
    {
        $attendance = Attendance::factory()->create();

        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();

        $this->assertFalse($guru->can('update', $attendance));
        $this->assertTrue($piket->can('update', $attendance));
    }

    public function test_inactive_account_cannot_access_panel(): void
    {
        $user = User::where('email', 'guru1@presensi.test')->firstOrFail();
        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
