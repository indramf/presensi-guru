<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TimetablePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'school.days' => [6, 7, 1],
            'school.periods' => [
                ['start' => '07:30', 'end' => '08:10', 'type' => 'lesson'],
                ['start' => '08:10', 'end' => '08:30', 'type' => 'break'],
                ['start' => '08:30', 'end' => '09:10', 'type' => 'lesson'],
            ],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_only_piket_and_admin_can_open_the_board_in_every_mode(): void
    {
        foreach (['/admin/schedule-board', '/admin/schedule-board?mode=class', '/admin/schedule-board?mode=day'] as $url) {
            foreach (['admin@presensi.test' => 200, 'piket@presensi.test' => 200, 'guru1@presensi.test' => 403] as $email => $status) {
                $this->flushSession();

                $this->actingAs(User::where('email', $email)->firstOrFail())->get($url)->assertStatus($status);
            }
        }
    }

    public function test_class_mode_shows_only_the_selected_class(): void
    {
        $chosen = SchoolClass::factory()->create(['class_name' => 'X RPL 9']);
        $other = SchoolClass::factory()->create(['class_name' => 'XI TKJ 7']);

        $this->actingAs(User::where('email', 'piket@presensi.test')->firstOrFail())
            ->get('/admin/schedule-board?mode=class&classId='.$chosen->id)
            ->assertOk()
            ->assertSee('Jadwal Kelas X RPL 9')
            ->assertDontSee('Jadwal Kelas XI TKJ 7');
    }

    public function test_day_mode_shows_classes_as_columns(): void
    {
        $class = SchoolClass::factory()->create(['class_name' => 'X RPL 9']);
        Schedule::factory()->create([
            'class_id' => $class->id,
            'day_of_week' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:10:00',
            'subject_id' => Subject::factory()->create(['subject_name' => 'Fisika Uji']),
        ]);

        $this->actingAs(User::where('email', 'admin@presensi.test')->firstOrFail())
            ->get('/admin/schedule-board?mode=day&day=1')
            ->assertOk()
            ->assertSee('Jadwal Hari Senin')
            ->assertSee('X RPL 9')
            ->assertSee('Fisika Uji');
    }

    public function test_mismatched_schedule_is_listed_under_check_data(): void
    {
        $class = SchoolClass::factory()->create();
        Schedule::factory()->create([
            'class_id' => $class->id,
            'day_of_week' => 1,
            'start_time' => '07:31:00',
            'end_time' => '08:10:00',
        ]);

        $this->actingAs(User::where('email', 'piket@presensi.test')->firstOrFail())
            ->get('/admin/schedule-board?mode=day&day=1')
            ->assertSee('periksa data');
    }
}
