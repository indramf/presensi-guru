<?php

namespace App\Services;

use App\Enums\MonitoringStatus;
use App\Models\Attendance;
use App\Models\ClassReport;
use App\Models\Schedule;

final class MonitoringRow
{
    public function __construct(
        public readonly Schedule $schedule,
        public readonly ?Attendance $attendance,
        public readonly ?ClassReport $report,
        public readonly MonitoringStatus $status,
        public readonly bool $needsAction,
    ) {}
}
