<?php

namespace Database\Factories;

use App\Enums\AlertSeverity;
use App\Enums\AlertStatus;
use App\Enums\AnomalyRule;
use App\Models\SecurityAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityAlert>
 */
class SecurityAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rule_code' => AnomalyRule::An01,
            'severity' => AlertSeverity::Sedang,
            'description' => 'Kegagalan login berulang dalam waktu singkat',
            'detected_at' => now(),
            'status' => AlertStatus::Baru,
        ];
    }
}
