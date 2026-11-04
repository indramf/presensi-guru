<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Services\Anomaly\AlertData;
use App\Services\Anomaly\Detector;

abstract class AttendanceRejectionRule implements Detector
{
    abstract protected function rule(): AnomalyRule;

    /** @return list<string> kode alasan penolakan yang dicakup */
    abstract protected function reasons(): array;

    abstract protected function title(): string;

    public function evaluate(AuditLog $entry): ?AlertData
    {
        if ($entry->event_type !== AuditEventType::AttendanceRejected
            || ! in_array($entry->reason, $this->reasons(), true)) {
            return null;
        }

        $date = $entry->after_data['attendance_date'] ?? '-';

        return new AlertData(
            $this->rule(),
            AlertSeverity::Rendah,
            "{$this->title()} (jadwal #{$entry->target_id}, tanggal {$date}).",
            $entry->actor_user_id,
            $entry->id,
        );
    }
}