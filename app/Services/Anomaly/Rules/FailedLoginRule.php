<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Services\Anomaly\AlertData;
use App\Services\Anomaly\Detector;

class FailedLoginRule implements Detector
{
    public function evaluate(AuditLog $entry): ?AlertData
    {
        if ($entry->event_type !== AuditEventType::LoginFailed) {
            return null;
        }

        $login = $entry->after_data['login'] ?? null;

        if ($login === null || $login === '') {
            return null;
        }

        $cfg = config('anomaly.an01');

        $count = AuditLog::query()
            ->where('event_type', AuditEventType::LoginFailed->value)
            ->where('after_data->login', $login)
            ->where('id', '<=', $entry->id)
            ->where('occurred_at', '>=', $entry->occurred_at->copy()->subMinutes($cfg['window_minutes']))
            ->count();

        if ($count < $cfg['threshold']) {
            return null;
        }

        return new AlertData(
            AnomalyRule::An01,
            AlertSeverity::Sedang,
            "Kegagalan login berulang untuk akun {$login} ({$cfg['threshold']} kali atau lebih dalam {$cfg['window_minutes']} menit).",
            $entry->actor_user_id,
            $entry->id,
            $cfg['dedupe_minutes'],
        );
    }
}
