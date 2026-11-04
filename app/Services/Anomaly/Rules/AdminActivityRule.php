<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Services\Anomaly\AlertData;
use App\Services\Anomaly\Detector;

class AdminActivityRule implements Detector
{
    private const TARGETS = ['User', 'Teacher', 'SchoolClass', 'Subject', 'Schedule'];

    public function evaluate(AuditLog $entry): ?AlertData
    {
        $types = [
            AuditEventType::DataCreated,
            AuditEventType::DataUpdated,
            AuditEventType::DataDeactivated,
        ];

        if (
            ! in_array($entry->event_type, $types, true)
            || ! in_array($entry->target_type, self::TARGETS, true)
            || $entry->actor_user_id === null
        ) {
            return null;
        }

        $cfg = config('anomaly.an07');
        $user = $entry->actor_user_id;

        $count = AuditLog::query()
            ->whereIn('event_type', array_map(fn ($t) => $t->value, $types))
            ->whereIn('target_type', self::TARGETS)
            ->where('actor_user_id', $user)
            ->where('id', '<=', $entry->id)
            ->where(
                'occurred_at',
                '>=',
                $entry->occurred_at->copy()->subMinutes($cfg['window_minutes'])
            )
            ->count();

        if ($count >= $cfg['burst_threshold']) {
            return new AlertData(
                AnomalyRule::An07,
                AlertSeverity::Sedang,
                "{$cfg['burst_threshold']} atau lebih perubahan data administratif dalam {$cfg['window_minutes']} menit oleh pengguna #{$user}.",
                $user,
                $entry->id,
                $cfg['window_minutes'],
            );
        }

        [$open, $close] = $cfg['office_hours'];
        $time = $entry->occurred_at->format('H:i');

        if ($time < $open || $time >= $close) {
            return new AlertData(
                AnomalyRule::An07,
                AlertSeverity::Rendah,
                "Aktivitas administratif di luar jam kerja oleh pengguna #{$user} pada {$entry->occurred_at->toDateString()}.",
                $user,
                $entry->id,
                1440,
            );
        }

        return null;
    }
}
