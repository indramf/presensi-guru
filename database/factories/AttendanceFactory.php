<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            // guru presensi = guru pada jadwal
            'teacher_id' => fn (array $attributes) => Schedule::find($attributes['schedule_id'])->teacher_id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::Hadir,
            'recorded_by' => User::factory(),
            'recorded_at' => now(),
        ];
    }
}
