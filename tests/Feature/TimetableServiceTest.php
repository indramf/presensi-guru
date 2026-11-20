<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TimetableServiceTest extends TestCase
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
    }

    private function schedule(SchoolClass $class, int $day, string $start, string $end, array $extra = []): Schedule
    {
        return Schedule::factory()->create([
            'class_id' => $class->id,
            'day_of_week' => $day,
            'start_time' => $start.':00',
            'end_time' => $end.':00',
        ] + $extra);
    }

    public function test_class_timetable_puts_subject_and_teacher_in_the_right_cell(): void
    {
        $class = SchoolClass::factory()->create();
        $this->schedule($class, 1, '08:30', '09:10', [
            'teacher_id' => Teacher::factory()->create(['full_name' => 'Budi Uji']),
            'subject_id' => Subject::factory()->create(['subject_name' => 'Matematika Uji']),
        ]);

        $result = app(TimetableService::class)->forClass($class);

        // Kolom hari [6, 7, 1]: Senin = indeks 2. Baris jam: 08:30 = indeks 2.
        $this->assertSame([['subject' => 'Matematika Uji', 'teacher' => 'Budi Uji']], $result['rows'][2]['cells'][2]);
        $this->assertSame([], $result['rows'][2]['cells'][0]);
        $this->assertSame(['Sabtu', 'Minggu', 'Senin'], array_column($result['days'], 'name'));
    }

    public function test_break_rows_are_marked_and_have_no_cells(): void
    {
        $result = app(TimetableService::class)->forClass(SchoolClass::factory()->create());

        $this->assertSame('break', $result['rows'][1]['type']);
        $this->assertSame([], $result['rows'][1]['cells']);
        $this->assertSame('lesson', $result['rows'][0]['type']);
    }

    public function test_day_timetable_has_one_column_per_active_class_ordered_by_name(): void
    {
        $a = SchoolClass::factory()->create(['class_name' => 'A Kelas']);
        $b = SchoolClass::factory()->create(['class_name' => 'B Kelas']);
        SchoolClass::factory()->create(['class_name' => 'C Nonaktif', 'is_active' => false]);
        $this->schedule($b, 1, '07:30', '08:10', ['subject_id' => Subject::factory()->create(['subject_name' => 'Fisika Uji'])]);

        $result = app(TimetableService::class)->forDay(1);

        $this->assertSame(['A Kelas', 'B Kelas'], $result['columns']->pluck('class_name')->all());
        $this->assertSame([], $result['rows'][0]['cells'][0]);
        $this->assertSame('Fisika Uji', $result['rows'][0]['cells'][1][0]['subject']);
    }

    public function test_schedules_outside_periods_or_school_days_are_reported_not_hidden(): void
    {
        $class = SchoolClass::factory()->create();
        $this->schedule($class, 1, '07:31', '08:10');   // jam tidak cocok
        $this->schedule($class, 1, '08:10', '08:30');   // jatuh pada istirahat
        $this->schedule($class, 5, '07:30', '08:10');   // Jumat, bukan hari sekolah

        $result = app(TimetableService::class)->forClass($class);

        $this->assertCount(3, $result['unmatched']);
        $this->assertSame([], $result['rows'][0]['cells'][2]);
    }

    public function test_inactive_schedules_and_other_classes_are_not_shown(): void
    {
        $class = SchoolClass::factory()->create();
        $this->schedule($class, 1, '07:30', '08:10', ['is_active' => false]);
        $this->schedule(SchoolClass::factory()->create(), 1, '07:30', '08:10');

        $result = app(TimetableService::class)->forClass($class);

        $this->assertSame([], $result['rows'][0]['cells'][2]);
        $this->assertSame([], $result['unmatched']);
    }

    public function test_current_period_and_today_column_are_flagged(): void
    {
        $class = SchoolClass::factory()->create();
        $monday = Carbon::parse('2026-11-02 08:40:00');   // Senin

        $result = app(TimetableService::class)->forClass($class, $monday);

        $this->assertSame(2, $result['todayIndex']);
        $this->assertSame([false, false, true], array_column($result['rows'], 'current'));
    }

    public function test_query_count_stays_small(): void
    {
        $class = SchoolClass::factory()->create();

        foreach (range(1, 8) as $i) {
            $this->schedule(SchoolClass::factory()->create(), 1, '07:30', '08:10');
        }

        DB::enableQueryLog();
        app(TimetableService::class)->forDay(1);
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(6, $queries);
    }

    public function test_all_classes_view_has_one_block_per_active_class(): void
    {
        $a = SchoolClass::factory()->create(['class_name' => 'A Kelas']);
        $b = SchoolClass::factory()->create(['class_name' => 'B Kelas']);
        SchoolClass::factory()->create(['class_name' => 'C Nonaktif', 'is_active' => false]);

        $this->schedule($a, 6, '07:30', '08:10', ['subject_id' => Subject::factory()->create(['subject_name' => 'Fisika Uji'])]);
        $this->schedule($b, 1, '08:30', '09:10');
        $this->schedule($b, 5, '07:30', '08:10');   // Jumat: bukan hari sekolah

        $result = app(TimetableService::class)->forAllClasses();

        $this->assertSame(['A Kelas', 'B Kelas'], array_map(fn ($blk) => $blk['class']->class_name, $result['blocks']));
        $this->assertSame('Fisika Uji', $result['blocks'][0]['rows'][0]['cells'][0][0]['subject']);   // Sabtu = kolom 0
        $this->assertSame([], $result['blocks'][1]['rows'][0]['cells'][0]);
        $this->assertCount(1, $result['unmatched']);
    }

    public function test_all_classes_view_query_count_does_not_grow_with_classes(): void
    {
        foreach (range(1, 10) as $i) {
            $this->schedule(SchoolClass::factory()->create(), 1, '07:30', '08:10');
        }

        DB::enableQueryLog();
        app(TimetableService::class)->forAllClasses();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(6, $queries);
    }
}
