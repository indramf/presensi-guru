<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'type' => 'lesson',
            'activity_name' => null,
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
            'is_active' => true,
        ];
    }

    public function activity(string $name = 'Kegiatan Sekolah'): static
    {
        return $this->state(fn (array $attributes): array => [
            'teacher_id' => null,
            'subject_id' => null,
            'type' => 'activity',
            'activity_name' => $name,
        ]);
    }
}
