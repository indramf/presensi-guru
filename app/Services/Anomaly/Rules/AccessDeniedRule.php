<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Services\Anomaly\AlertData;
use App\Services\Anomaly\Detector;

class AccessDeniedRule implements Detector
{
    public function evaluate(AuditLog $entry): ?AlertData
    {
        if (
            $entry->event_type !== AuditEventType::AccessDenied
            || $entry->actor_user_id === null
        ) {
            return null;
        }

        $cfg = config('anomaly.an04');

        $count = AuditLog::query()
            ->where('event_type', AuditEventType::AccessDenied->value)
            ->where('actor_user_id', $entry->actor_user_id)
            ->where('id', '<=', $entry->id)
            ->where(
                'occurred_at',
                '>=',
                $entry->occurred_at->copy()->subMinutes($cfg['window_minutes'])
            )
            ->count();

        $repeated = $count >= $cfg['repeat_threshold'];
        $user = $entry->actor_user_id;

        return new AlertData(
            AnomalyRule::An04,
            $repeated ? AlertSeverity::Tinggi : AlertSeverity::Sedang,
            $repeated
                ? "Percobaan mengakses fungsi atau data tanpa hak berulang oleh pengguna #{$user}."
                : "Percobaan mengakses fungsi atau data tanpa hak oleh pengguna #{$user}.",
            $user,
            $entry->id,
            $cfg['dedupe_minutes'],
        );
    }
}