<?php

namespace App\Services\Anomaly\Rules;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Services\Anomaly\AlertData;
use App\Services\Anomaly\Detector;
use Carbon\Carbon;

class SuddenScheduleChangeRule implements Detector
{
    private const WATCHED = [
        'day_of_week',
        'start_time',
        'end_time',
        'teacher_id',
        'class_id',
        'is_active',
    ];

    public function evaluate(AuditLog $entry): ?AlertData
    {
        if (
            ! in_array($entry->event_type, [
                AuditEventType::DataUpdated,
                AuditEventType::DataDeactivated,
            ], true)
            || $entry->target_type !== 'Schedule'
            || $entry->target_id === null
        ) {
            return null;
        }

        $before = $entry->before_data ?? [];
        $after = $entry->after_data ?? [];

        if (array_intersect(array_keys($after), self::WATCHED) === []) {
            return null;
        }

        $schedule = Schedule::find($entry->target_id);

        if ($schedule === null) {
            return null;
        }

        $when = $entry->occurred_at;
        $day = (int) ($before['day_of_week'] ?? $schedule->day_of_week);

        // Perubahan yang bukan pada hari berlakunya jadwal tidak dianggap mendadak.
        if ($when->dayOfWeekIso !== $day) {
            return null;
        }

        $tz = $when->getTimezone();

        $start = Carbon::parse(
            $when->toDateString().' '.($before['start_time'] ?? $schedule->start_time),
            $tz
        );

        $end = Carbon::parse(
            $when->toDateString().' '.($before['end_time'] ?? $schedule->end_time),
            $tz
        );

        $hours = (int) config('anomaly.an06.sudden_change_hours');

        if ($when->lt($start->copy()->subHours($hours)) || $when->gt($end)) {
            return null;
        }

        return new AlertData(
            AnomalyRule::An06,
            AlertSeverity::Rendah,
            "Jadwal #{$schedule->id} diubah pada hari berlakunya (entri log #{$entry->id}); periksa apakah perubahan sesuai kebutuhan.",
            $entry->actor_user_id,
            $entry->id,
            1,
        );
    }
}