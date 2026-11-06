<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleConflictChecker
{
    /** @return list<string> pesan masalah; kosong berarti jadwal valid */
    public function check(
        ?int $teacherId,
        int $classId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreScheduleId = null,
    ): array {
        $start = Carbon::parse($startTime)->format('H:i:s');
        $end = Carbon::parse($endTime)->format('H:i:s');

        if ($end <= $start) {
            return ['Jam selesai harus setelah jam mulai.'];
        }

        $overlapping = Schedule::query()
            ->active()
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreScheduleId, fn ($q) => $q->whereKeyNot($ignoreScheduleId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        $messages = [];

        if ($teacherId !== null && (clone $overlapping)->where('teacher_id', $teacherId)->exists()) {
            $messages[] = 'Guru sudah punya jadwal lain yang beririsan pada hari dan jam ini.';
        }

        if ((clone $overlapping)->where('class_id', $classId)->exists()) {
            $messages[] = 'Kelas sudah punya jadwal lain yang beririsan pada hari dan jam ini.';
        }

        return $messages;
    }
}
