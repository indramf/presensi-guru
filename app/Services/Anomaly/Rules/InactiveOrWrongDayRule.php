<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AnomalyRule;

class InactiveOrWrongDayRule extends AttendanceRejectionRule
{
    protected function rule(): AnomalyRule
    {
        return AnomalyRule::An06;
    }

    protected function reasons(): array
    {
        return ['inactive_schedule', 'wrong_day'];
    }

    protected function title(): string
    {
        return 'Presensi tidak sesuai jadwal aktif';
    }
}
