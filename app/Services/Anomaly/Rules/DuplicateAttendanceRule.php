<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AnomalyRule;

class DuplicateAttendanceRule extends AttendanceRejectionRule
{
    protected function rule(): AnomalyRule
    {
        return AnomalyRule::An03;
    }

    protected function reasons(): array
    {
        return ['duplicate'];
    }

    protected function title(): string
    {
        return 'Presensi ganda untuk sesi dan tanggal yang sama';
    }
}
