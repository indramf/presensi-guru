<?php

namespace Tests\Feature;

use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MasterDataResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_fr04_admin_can_deactivate_a_teacher_through_the_form(): void
    {
        $this->actingAs(User::where('email', 'admin@presensi.test')->firstOrFail());
        $teacher = Teacher::firstOrFail();

        Livewire::test(EditTeacher::class, ['record' => $teacher->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($teacher->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'target_type' => 'Teacher',
            'target_id' => $teacher->id,
            'event_type' => 'data.deactivated',
        ]);
    }
}
