<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Services\ScheduleConflictChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleConflictTest extends TestCase
{
    use RefreshDatabase;

    private function existing(): Schedule
    {
        return Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);
    }

    public function test_fr05_same_teacher_overlapping_is_rejected(): void
    {
        $s = $this->existing();

        $this->assertNotEmpty(app(ScheduleConflictChecker::class)
            ->check($s->teacher_id, 999, 1, '07:30', '08:15'));
    }

    public function test_fr05_same_class_overlapping_is_rejected(): void
    {
        $s = $this->existing();

        $this->assertNotEmpty(app(ScheduleConflictChecker::class)
            ->check(999, $s->class_id, 1, '07:00', '07:45'));
    }

    public function test_fr05_adjacent_slot_is_allowed(): void
    {
        $s = $this->existing();

        $this->assertSame([], app(ScheduleConflictChecker::class)
            ->check($s->teacher_id, $s->class_id, 1, '07:45', '08:30'));
    }

    public function test_fr05_different_day_is_allowed(): void
    {
        $s = $this->existing();

        $this->assertSame([], app(ScheduleConflictChecker::class)
            ->check($s->teacher_id, $s->class_id, 2, '07:00', '07:45'));
    }

    public function test_fr05_editing_own_schedule_is_not_a_conflict(): void
    {
        $s = $this->existing();

        $this->assertSame([], app(ScheduleConflictChecker::class)
            ->check($s->teacher_id, $s->class_id, 1, '07:00', '07:45', $s->id));
    }

    public function test_fr05_end_before_start_is_rejected(): void
    {
        $this->assertNotEmpty(app(ScheduleConflictChecker::class)->check(1, 1, 1, '08:00', '07:00'));
    }

    public function test_inactive_schedules_are_ignored(): void
    {
        $s = $this->existing();
        $s->update(['is_active' => false]);

        $this->assertSame([], app(ScheduleConflictChecker::class)
            ->check($s->teacher_id, $s->class_id, 1, '07:00', '07:45'));
    }
}
