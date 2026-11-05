<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\Monitoring;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MonitoringPageUsabilityTest extends TestCase
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

    public function test_actionable_sessions_are_shown_first_with_schedule_details(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 07:30:00', 'Asia/Jakarta'));

        $user = User::factory()->create();
        $user->assignRole(UserRole::GuruPiket->value);

        Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
            'class_id' => SchoolClass::factory()->create(['class_name' => '10A Mendesak']),
            'subject_id' => Subject::factory()->create(['subject_name' => 'Matematika Mendesak']),
        ]);
        Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:15:00',
            'class_id' => SchoolClass::factory()->create(['class_name' => '10B Normal']),
            'subject_id' => Subject::factory()->create(['subject_name' => 'IPA Normal']),
        ]);

        $this->actingAs($user);

        Livewire::test(Monitoring::class)
            ->assertSee('aria-label="Tampilkan 1 sesi yang perlu tindakan"', false)
            ->assertSee('Matematika Mendesak')
            ->assertSee('10A Mendesak')
            ->assertSee('IPA Normal')
            ->assertSee('10B Normal')
            ->assertSee('data-needs-action="true"', false)
            ->assertSeeInOrder(['10A Mendesak', '10B Normal']);
    }
}
