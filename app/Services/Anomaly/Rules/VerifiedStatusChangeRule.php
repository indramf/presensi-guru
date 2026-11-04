<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Services\Anomaly\AlertData;
use App\Services\Anomaly\Detector;

class VerifiedStatusChangeRule implements Detector
{
    public function evaluate(AuditLog $entry): ?AlertData
    {
        if (
            $entry->event_type !== AuditEventType::AttendanceStatusChanged
            || ($entry->before_data['verified'] ?? false) !== true
        ) {
            return null;
        }

        $from = $entry->before_data['status'] ?? '?';
        $to = $entry->after_data['status'] ?? '?';

        return new AlertData(
            AnomalyRule::An05,
            AlertSeverity::Sedang,
            "Status presensi #{$entry->target_id} diubah dari {$from} ke {$to} setelah diverifikasi (entri log #{$entry->id}).",
            $entry->actor_user_id,
            $entry->id,
            1,
        );
    }
}