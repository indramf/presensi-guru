<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\MyAttendance;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MyAttendancePageTest extends TestCase
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

    public function test_teacher_only_sees_lesson_schedules_not_school_activities(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 07:30:00', 'Asia/Jakarta'));

        $user = User::factory()->create();
        $user->assignRole(UserRole::Guru->value);

        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
        ]);

        Schedule::factory()->create([
            'teacher_id' => $teacher->id,
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);

        Schedule::factory()
            ->activity('Upacara Bendera')
            ->create([
                'day_of_week' => 1,
                'start_time' => '07:45:00',
                'end_time' => '08:30:00',
            ]);

        $this->actingAs($user);

        Livewire::test(MyAttendance::class)
            ->assertSee('Isi Presensi')
            ->assertDontSee('Upacara Bendera');
    }
}
