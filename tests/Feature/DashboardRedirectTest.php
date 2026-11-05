<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Monitoring;
use App\Filament\Pages\MyAttendance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guru_is_redirected_to_personal_attendance(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Guru->value);

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertRedirect(MyAttendance::getUrl());
    }

    public function test_guru_piket_and_administrator_are_redirected_to_monitoring(): void
    {
        foreach ([UserRole::GuruPiket, UserRole::Administrator] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role->value);

            $this->actingAs($user);

            Livewire::test(Dashboard::class)
                ->assertRedirect(Monitoring::getUrl());
        }
    }

    public function test_user_without_a_destination_sees_an_explanation(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Dashboard::class)
            ->assertSee('Halaman utama belum tersedia');
    }
}
