<?php

namespace Tests\Feature;

use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_fr05_creating_and_updating_master_data_is_audited(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $teacher = Teacher::factory()->create(['full_name' => 'Guru Uji']);
        $teacher->update(['full_name' => 'Nama Baru']);

        $created = AuditLog::where('target_type', 'Teacher')
            ->where('event_type', AuditEventType::DataCreated->value)->firstOrFail();
        $this->assertSame($admin->id, $created->actor_user_id);

        $updated = AuditLog::where('event_type', AuditEventType::DataUpdated->value)->firstOrFail();
        $this->assertSame('Guru Uji', $updated->before_data['full_name']);
        $this->assertSame('Nama Baru', $updated->after_data['full_name']);
    }

    public function test_fr04_deactivating_is_logged_as_deactivation(): void
    {
        $this->actingAs(User::factory()->create());
        $teacher = Teacher::factory()->create();

        $teacher->update(['is_active' => false]);

        $this->assertTrue(
            AuditLog::where('event_type', AuditEventType::DataDeactivated->value)->exists()
        );
    }

    public function test_nfr05_password_hash_is_never_written_to_audit_log(): void
    {
        $this->actingAs(User::factory()->create());

        User::factory()->create();

        $log = AuditLog::where('target_type', 'User')->latest('id')->firstOrFail();
        $this->assertArrayNotHasKey('password', $log->after_data);
        $this->assertArrayNotHasKey('remember_token', $log->after_data);
    }

    public function test_changes_without_login_are_not_logged(): void
    {
        Teacher::factory()->create();

        $this->assertSame(0, AuditLog::count());
    }
}
