<?php

namespace Tests\Feature;

use App\Enums\AlertStatus;
use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\AuditVerificationService;
use App\Services\SecurityAlertService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecurityAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $checkpointPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkpointPath = sys_get_temp_dir()
            .DIRECTORY_SEPARATOR.'audit-cp-'.uniqid().'.json';

        config(['audit.checkpoint_path' => $this->checkpointPath]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        @unlink($this->checkpointPath);
        parent::tearDown();
    }

    private function user(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }

    public function test_fr15_admin_moves_alert_forward_and_reviewer_and_time_are_saved(): void
    {
        $admin = $this->user(UserRole::Administrator);
        $alert = SecurityAlert::factory()->create();

        $service = app(SecurityAlertService::class);

        $reviewed = $service->changeStatus(
            $admin,
            $alert,
            AlertStatus::Ditinjau,
            'Sedang diperiksa',
        );

        $this->assertSame(AlertStatus::Ditinjau, $reviewed->status);
        $this->assertSame($admin->id, $reviewed->reviewed_by);
        $this->assertNotNull($reviewed->reviewed_at);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => AuditEventType::AlertReviewed->value,
            'actor_user_id' => $admin->id,
            'target_type' => 'SecurityAlert',
            'target_id' => $alert->id,
        ]);

        $closed = $service->changeStatus(
            $admin,
            $reviewed,
            AlertStatus::Ditutup,
            'Pemeriksaan selesai',
        );

        $this->assertSame(AlertStatus::Ditutup, $closed->status);
    }

    public function test_fr15_status_cannot_move_backwards(): void
    {
        $admin = $this->user(UserRole::Administrator);

        $alert = SecurityAlert::factory()->create([
            'status' => AlertStatus::Ditutup,
        ]);

        $this->expectException(ValidationException::class);

        app(SecurityAlertService::class)->changeStatus(
            $admin,
            $alert,
            AlertStatus::Baru,
        );
    }

    public function test_fr15_piket_and_guru_cannot_review_alerts(): void
    {
        $alert = SecurityAlert::factory()->create();

        foreach ([UserRole::Guru, UserRole::GuruPiket] as $role) {
            $user = $this->user($role);

            try {
                app(SecurityAlertService::class)->changeStatus(
                    $user,
                    $alert,
                    AlertStatus::Ditinjau,
                );

                $this->fail("Role {$role->value} seharusnya tidak boleh meninjau alert.");
            } catch (AuthorizationException) {
                $this->assertSame(AlertStatus::Baru, $alert->fresh()->status);
            }
        }

        $this->assertSame(
            2,
            AuditLog::where('event_type', AuditEventType::AccessDenied->value)
                ->where('reason', 'alert_review_forbidden')
                ->count(),
        );
    }

    public function test_fr13_admin_can_run_integrity_check_and_it_is_logged(): void
    {
        $admin = $this->user(UserRole::Administrator);

        app(AuditLogger::class)->log(
            AuditEventType::DataCreated,
            AuditResult::Success,
            actorId: $admin->id,
            targetType: 'Teacher',
            targetId: 1,
            after: ['name' => 'Guru Test'],
        );

        $result = app(AuditVerificationService::class)->run($admin);

        $this->assertTrue($result['ok'], implode(' | ', $result['problems']));
        $this->assertSame(1, $result['checked']);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => AuditEventType::IntegrityChecked->value,
            'actor_user_id' => $admin->id,
            'action_result' => AuditResult::Success->value,
        ]);
    }

    public function test_fr13_non_admin_cannot_run_integrity_check(): void
    {
        $guru = $this->user(UserRole::Guru);

        $this->expectException(AuthorizationException::class);

        app(AuditVerificationService::class)->run($guru);
    }
}
