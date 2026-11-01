<?php

namespace Database\Factories;

use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassReport>
 */
class ClassReportFactory extends Factory
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
            'report_date' => now()->toDateString(),
            'reported_by' => User::factory(),
            'report_status' => ClassReportStatus::KelasKosong,
            'description' => fake()->sentence(),
            'follow_up_status' => FollowUpStatus::Belum,
        ];
    }
}
