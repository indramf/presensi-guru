<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ScheduleBoardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduleBoardServiceTest extends TestCase
{
    use RefreshDatabase;

    /** 2 November 2026 adalah hari Senin. */
    private function at(string $time, string $date = '2026-11-02'): Carbon
    {
        return Carbon::parse("{$date} {$time}");
    }

    private function board(): ScheduleBoardService
    {
        return app(ScheduleBoardService::class);
    }

    public function test_slots_are_grouped_per_hour_and_ordered_by_time(): void
    {
        Schedule::factory()->create(['day_of_week' => 1, 'start_time' => '07:45:00', 'end_time' => '08:30:00']);
        Schedule::factory()->count(2)->create(['day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00']);

        $slots = $this->board()->slotsFor($this->at('06:00:00'), $this->at('06:00:00'));

        $this->assertSame(['07:00 - 07:45', '07:45 - 08:30'], $slots->pluck('label')->all());
        $this->assertCount(2, $slots[0]['rows']);
        $this->assertCount(1, $slots[1]['rows']);
    }

    public function test_each_row_shows_class_teacher_and_subject(): void
    {
        Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
            'teacher_id' => Teacher::factory()->create(['full_name' => 'Budi Uji']),
            'class_id' => SchoolClass::factory()->create(['class_name' => 'X RPL 9']),
            'subject_id' => Subject::factory()->create(['subject_name' => 'Matematika Uji']),
        ]);

        $row = $this->board()->slotsFor($this->at('06:00:00'))->first()['rows']->first();

        $this->assertSame(['class' => 'X RPL 9', 'teacher' => 'Budi Uji', 'subject' => 'Matematika Uji'], $row);
    }

    public function test_only_active_schedules_of_that_weekday_are_listed(): void
    {
        Schedule::factory()->create(['day_of_week' => 1]);
        Schedule::factory()->create(['day_of_week' => 2]);                       // Selasa
        Schedule::factory()->create(['day_of_week' => 1, 'is_active' => false]); // nonaktif

        $this->assertSame(1, $this->board()->slotsFor($this->at('06:00:00'))->flatMap(fn ($s) => $s['rows'])->count());
    }

    public function test_day_without_schedules_returns_empty_board(): void
    {
        Schedule::factory()->create(['day_of_week' => 1]);

        $this->assertCount(0, $this->board()->slotsFor($this->at('06:00:00', '2026-11-01')));   // Minggu
    }

    public function test_running_slot_is_flagged_and_adjacent_slots_are_not_both_running(): void
    {
        Schedule::factory()->create(['day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00']);
        Schedule::factory()->create(['day_of_week' => 1, 'start_time' => '07:45:00', 'end_time' => '08:30:00']);

        $during = $this->board()->slotsFor($this->at('07:10:00'), $this->at('07:10:00'));
        $this->assertSame([true, false], $during->pluck('running')->all());

        $boundary = $this->board()->slotsFor($this->at('07:45:00'), $this->at('07:45:00'));
        $this->assertSame([false, true], $boundary->pluck('running')->all());
    }

    public function test_query_count_does_not_grow_with_number_of_schedules(): void
    {
        Schedule::factory()->count(15)->create(['day_of_week' => 1]);

        DB::enableQueryLog();
        $this->board()->slotsFor($this->at('06:00:00'));
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(5, $queries);
    }
}
