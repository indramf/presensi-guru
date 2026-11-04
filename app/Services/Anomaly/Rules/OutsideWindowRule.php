<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AnomalyRule;

class OutsideWindowRule extends AttendanceRejectionRule
{
    protected function rule(): AnomalyRule
    {
        return AnomalyRule::An02;
    }

    protected function reasons(): array
    {
        return ['outside_window'];
    }

    protected function title(): string
    {
        return 'Presensi dikirim di luar rentang waktu sesi';
    }
}
