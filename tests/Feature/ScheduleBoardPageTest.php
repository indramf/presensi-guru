<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ScheduleBoardPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_only_piket_and_admin_can_open_the_board(): void
    {
        foreach (
            [
                'admin@presensi.test' => 200,
                'piket@presensi.test' => 200,
                'guru1@presensi.test' => 403,
            ] as $email => $status
        ) {
            $this->flushSession();

            $user = User::where('email', $email)->firstOrFail();

            $this->actingAs($user)
                ->get('/admin/schedule-board')
                ->assertStatus($status);
        }
    }

    public function test_board_lists_class_teacher_and_subject(): void
    {
        Carbon::setTestNow('2026-11-02 07:40:00');

        Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:10:00',
            'teacher_id' => Teacher::factory()->create([
                'full_name' => 'Budi Uji Schedule Board',
            ])->id,
            'class_id' => SchoolClass::factory()->create([
                'class_name' => 'X RPL Schedule Board',
            ])->id,
            'subject_id' => Subject::factory()->create([
                'subject_name' => 'Matematika Uji Schedule Board',
            ])->id,
        ]);

        $user = User::where('email', 'piket@presensi.test')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin/schedule-board')
            ->assertOk()
            ->assertSee('07:30 - 08:10')
            ->assertSee('X RPL Schedule Board')
            ->assertSee('Budi Uji Schedule Board')
            ->assertSee('Matematika Uji Schedule Board')
            ->assertSee('Sedang berlangsung');
    }
}
