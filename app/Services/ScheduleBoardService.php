<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ScheduleBoardService
{
    /**
     * Urutan hari sekolah: Sabtu sampai Kamis.
     */
    public const DAYS = [
        6 => 'Sabtu',
        7 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
    ];

    /**
     * Daftar kelas aktif.
     */
    public function activeClasses(): Collection
    {
        return SchoolClass::query()
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();
    }

    /**
     * Jadwal mingguan seluruh kelas aktif.
     */
    public function allClassesFor(?CarbonInterface $now = null): Collection
    {
        $now ??= now();

        $classes = $this->activeClasses();

        if ($classes->isEmpty()) {
            return collect();
        }

        $schedules = $this->baseScheduleQuery()
            ->whereIn('class_id', $classes->pluck('id'))
            ->get();

        return $classes->map(function (SchoolClass $class) use ($schedules, $now) {
            $classSchedules = $schedules
                ->where('class_id', $class->id)
                ->values();

            return [
                'id' => $class->id,
                'name' => $class->class_name,
                'slots' => $this->weeklyRows($classSchedules, $now),
            ];
        });
    }

    /**
     * Jadwal mingguan satu kelas.
     */
    public function classScheduleFor(
        int $classId,
        ?CarbonInterface $now = null
    ): ?array {
        $now ??= now();

        $class = SchoolClass::query()
            ->where('is_active', true)
            ->find($classId);

        if (! $class) {
            return null;
        }

        $schedules = $this->baseScheduleQuery()
            ->where('class_id', $classId)
            ->get();

        return [
            'id' => $class->id,
            'name' => $class->class_name,
            'slots' => $this->weeklyRows($schedules, $now),
        ];
    }

    /**
     * Jadwal seluruh kelas pada satu hari.
     */
    public function dailyScheduleFor(
        int $dayOfWeek,
        ?CarbonInterface $now = null
    ): Collection {
        $now ??= now();

        if (! array_key_exists($dayOfWeek, self::DAYS)) {
            return collect();
        }

        $schedules = $this->baseScheduleQuery()
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('schoolClass', fn ($query) => $query->where('is_active', true))
            ->get();

        return $schedules
            ->groupBy(fn (Schedule $schedule) => $schedule->start_time.'|'.$schedule->end_time)
            ->map(function (Collection $group, string $key) use ($now, $dayOfWeek) {
                [$start, $end] = explode('|', $key);

                return [
                    'label' => $this->timeLabel($start, $end),
                    'running' => $this->isRunning($dayOfWeek, $start, $end, $now),
                    'rows' => $group
                        ->sortBy(fn (Schedule $schedule) => $schedule->schoolClass?->class_name ?? '')
                        ->values()
                        ->map(fn (Schedule $schedule) => [
                            'class' => $schedule->schoolClass?->class_name ?? 'Kelas tidak ditemukan',
                            'teacher' => $schedule->type === 'activity'
                                ? '—'
                                : ($schedule->teacher?->full_name ?? 'Guru tidak ditemukan'),
                            'subject' => $schedule->type === 'activity'
                                ? ($schedule->activity_name ?? 'Kegiatan sekolah')
                                : ($schedule->subject?->subject_name ?? 'Mata pelajaran tidak ditemukan'),
                        ]),
                ];
            })
            ->sortBy('label')
            ->values();
    }

    public function dailyCrossTableFor(
        int $dayOfWeek,
        ?CarbonInterface $now = null
    ): array {
        $now ??= now();

        $classes = $this->activeClasses();

        if (! array_key_exists($dayOfWeek, self::DAYS)) {
            return [
                'classes' => $classes,
                'rows' => collect(),
            ];
        }

        $schedules = $this->baseScheduleQuery()
            ->where('day_of_week', $dayOfWeek)
            ->whereIn('class_id', $classes->pluck('id'))
            ->get();

        $rows = $schedules
            ->groupBy(fn (Schedule $schedule) => $schedule->start_time.'|'.$schedule->end_time)
            ->map(function (Collection $group, string $key) use ($now) {
                [$start, $end] = explode('|', $key);

                return [
                    'start' => $start,
                    'end' => $end,
                    'label' => $this->timeLabel($start, $end),
                    'is_break' => false,
                    'cells' => $group
                        ->keyBy('class_id')
                        ->map(fn (Schedule $schedule) => $this->formatSchedule($schedule, $now)),
                ];
            });

        // Waktu istirahat tetap untuk seluruh kelas dan hari sekolah.
        $breaks = [
            ['10:10:00', '10:30:00'],
            ['11:50:00', '13:15:00'],
        ];

        foreach ($breaks as [$start, $end]) {
            $rows->put($start.'|'.$end, [
                'start' => $start,
                'end' => $end,
                'label' => $this->timeLabel($start, $end),
                'is_break' => true,
                'cells' => collect(),
            ]);
        }

        return [
            'classes' => $classes,
            'rows' => $rows->sortBy('start')->values(),
        ];
    }

    /**
     * Format tabel mingguan untuk satu kelas.
     */
    private function weeklyRows(
        Collection $schedules,
        CarbonInterface $now
    ): Collection {
        $slots = $schedules
            ->unique(fn (Schedule $schedule) => $schedule->start_time.'|'.$schedule->end_time)
            ->sortBy(fn (Schedule $schedule) => $schedule->start_time)
            ->values();

        $scheduleMap = $schedules->keyBy(
            fn (Schedule $schedule) => $schedule->day_of_week.'|'.$schedule->start_time.'|'.$schedule->end_time
        );

        return $slots->map(function (Schedule $slot) use ($scheduleMap, $now) {
            $days = collect(self::DAYS)->mapWithKeys(
                function (string $dayName, int $dayNumber) use ($slot, $scheduleMap, $now) {
                    $schedule = $scheduleMap->get(
                        $dayNumber.'|'.$slot->start_time.'|'.$slot->end_time
                    );

                    return [
                        $dayNumber => $schedule
                            ? $this->formatSchedule($schedule, $now)
                            : null,
                    ];
                }
            );

            return [
                'label' => $this->timeLabel($slot->start_time, $slot->end_time),
                'days' => $days,
            ];
        });
    }

    /**
     * Format satu data jadwal.
     */
    private function formatSchedule(
        Schedule $schedule,
        CarbonInterface $now
    ): array {
        $isActivity = $schedule->type === 'activity';

        return [
            'type' => $schedule->type,
            'title' => $isActivity
                ? ($schedule->activity_name ?? 'Kegiatan sekolah')
                : ($schedule->subject?->subject_name ?? 'Mata pelajaran tidak ditemukan'),
            'teacher' => $isActivity
                ? null
                : ($schedule->teacher?->full_name ?? 'Guru tidak ditemukan'),
            'running' => $this->isRunning(
                (int) $schedule->day_of_week,
                $schedule->start_time,
                $schedule->end_time,
                $now
            ),
        ];
    }

    /**
     * Query dasar dengan eager loading untuk mencegah N+1.
     */
    private function baseScheduleQuery()
    {
        return Schedule::query()
            ->active()
            ->whereIn('day_of_week', array_keys(self::DAYS))
            ->with(['teacher', 'schoolClass', 'subject'])
            ->orderBy('start_time')
            ->orderBy('end_time');
    }

    private function timeLabel(string $start, string $end): string
    {
        return substr($start, 0, 5).' - '.substr($end, 0, 5);
    }

    private function isRunning(
        int $dayOfWeek,
        string $start,
        string $end,
        CarbonInterface $now
    ): bool {
        if ($now->dayOfWeekIso !== $dayOfWeek) {
            return false;
        }

        $startAt = Carbon::parse($now->toDateString().' '.$start, $now->getTimezone());
        $endAt = Carbon::parse($now->toDateString().' '.$end, $now->getTimezone());

        return $now->gte($startAt) && $now->lt($endAt);
    }

    /**
     * Kompatibilitas dengan halaman Papan Jadwal harian lama.
     */
    public function slotsFor(
        ?CarbonInterface $date = null,
        ?CarbonInterface $now = null
    ): Collection {
        $now ??= now();
        $date ??= $now;

        if (! array_key_exists($date->dayOfWeekIso, self::DAYS)) {
            return collect();
        }

        return $this->dailyScheduleFor($date->dayOfWeekIso, $now);
    }
}
