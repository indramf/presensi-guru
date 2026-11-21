<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolClass;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Collection;

class TimetableService
{
    public const DAY_NAMES = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    /** @return list<array{start: string, end: string, label: string, type: string}> */
    public function periods(): array
    {
        return array_map(fn(array $p) => [
            'start' => $p['start'],
            'end' => $p['end'],
            'label' => $p['start'] . ' - ' . $p['end'],
            'type' => $p['type'],
        ], config('school.periods', []));
    }

    /** @return list<array{iso: int, name: string}> */
    public function days(): array
    {
        return array_map(
            fn(int $iso) => ['iso' => $iso, 'name' => self::DAY_NAMES[$iso] ?? (string) $iso],
            config('school.days', [1, 2, 3, 4, 5, 6]),
        );
    }

    public function dayName(int $iso): string
    {
        return self::DAY_NAMES[$iso] ?? (string) $iso;
    }

    public function classes(): Collection
    {
        return SchoolClass::query()->where('is_active', true)->orderBy('class_name')->get();
    }

    /** Jadwal mingguan satu kelas: baris = jam, kolom = hari. */
    public function forClass(SchoolClass $class, ?CarbonInterface $now = null): array
    {
        $days = $this->days();
        $isoDays = array_column($days, 'iso');

        $schedules = Schedule::query()
            ->active()
            ->where('class_id', $class->id)
            ->with(['teacher', 'subject', 'schoolClass'])
            ->get();

        $monitoringRows = $now !== null && in_array($now->dayOfWeekIso, $isoDays, true)
            ? app(MonitoringService::class)->sessionsFor($now, $now)
            ->keyBy(fn(MonitoringRow $row) => $row->schedule->id)
            : collect();

        $grid = $this->build($schedules, $isoDays, fn(Schedule $s) => $s->day_of_week, $now, $monitoringRows);

        return $grid + ['days' => $days, 'todayIndex' => $this->todayIndex($isoDays, $now)];
    }

    /** Jadwal mingguan satu guru: baris = jam, kolom = hari. */
    public function forTeacher(\App\Models\Teacher $teacher, ?CarbonInterface $now = null): array
    {
        $days = $this->days();
        $isoDays = array_column($days, 'iso');

        $schedules = Schedule::query()
            ->active()
            ->lessons()
            ->where('teacher_id', $teacher->id)
            ->whereIn('day_of_week', $isoDays)
            ->with(['subject', 'schoolClass'])
            ->get();

        $grid = $this->build(
            $schedules,
            $isoDays,
            fn(Schedule $s) => $s->day_of_week,
            $now,
            collect(),
        );

        return $grid + [
            'days' => $days,
            'todayIndex' => $this->todayIndex($isoDays, $now),
        ];
    }


    /** Jadwal satu hari: baris = jam, kolom = kelas aktif. */
    public function forDay(int $day, ?CarbonInterface $now = null): array
    {
        $classes = $this->classes();

        $schedules = Schedule::query()
            ->active()
            ->where('day_of_week', $day)
            ->whereIn('class_id', $classes->pluck('id'))
            ->with(['teacher', 'subject', 'schoolClass'])
            ->get();

        $monitoringRows = $now !== null && $day === $now->dayOfWeekIso
            ? app(MonitoringService::class)->sessionsFor($now, $now)
            ->keyBy(fn(MonitoringRow $row) => $row->schedule->id)
            : collect();

        $grid = $this->build($schedules, $classes->pluck('id')->all(), fn(Schedule $s) => $s->class_id, $now, $monitoringRows);

        return $grid + ['columns' => $classes];
    }

    private function build(
        Collection $schedules,
        array $columnKeys,
        Closure $columnOf,
        ?CarbonInterface $now,
        Collection $monitoringRows,
    ): array {

        $periods = $this->periods();

        $cells = [];
        $unmatched = [];

        foreach ($schedules as $s) {
            $start = substr($s->start_time, 0, 5);
            $end = substr($s->end_time, 0, 5);
            $column = $columnOf($s);

            $subjectName = $s->type === 'activity'
                ? ($s->activity_name ?: 'Kegiatan')
                : ($s->subject?->subject_name ?? 'Mata pelajaran belum ditentukan');

            $teacherName = $s->teacher?->full_name;
            $className = $s->schoolClass?->class_name ?? 'Kelas tidak diketahui';

            // Cari semua periode pelajaran yang dicakup oleh jadwal.
            $matchedPeriods = [];
            $cursor = $start;

            while ($cursor < $end) {
                $matchingPeriod = null;

                foreach ($periods as $period) {
                    if ($period['start'] === $cursor) {
                        $matchingPeriod = $period;
                        break;
                    }
                }

                // Jadwal tidak cocok, melewati istirahat,
                // atau tidak mengikuti batas periode.
                if (
                    $matchingPeriod === null ||
                    $matchingPeriod['type'] !== 'lesson' ||
                    $matchingPeriod['end'] > $end
                ) {
                    $matchedPeriods = [];
                    break;
                }

                $matchedPeriods[] = $matchingPeriod;
                $cursor = $matchingPeriod['end'];
            }

            // Pastikan jadwal berakhir tepat di batas periode.
            $isValidTime = count($matchedPeriods) > 0 && $cursor === $end;

            if (! $isValidTime || ! in_array($column, $columnKeys, true)) {
                $unmatched[] = [
                    'class' => $className,
                    'day' => $this->dayName($s->day_of_week),
                    'time' => str_replace('-', ' - ', $start . '-' . $end),
                    'teacher' => $teacherName,
                    'subject' => $subjectName,
                ];

                continue;
            }

            // Satu jadwal dapat mengisi beberapa periode.
            foreach ($matchedPeriods as $period) {
                $key = $period['start'] . '-' . $period['end'];

                $monitoring = $monitoringRows->get($s->id);

                $entry = [
                    'subject' => $subjectName,
                    'teacher' => $teacherName,
                    'class' => $className,
                ];

                if ($monitoring !== null) {
                    $entry['status'] = $monitoring->status->value;
                    $entry['status_label'] = $monitoring->status->label();
                    $entry['status_color'] = $monitoring->status->color();
                }

                $cells[$key][$column][] = $entry;
            }
        }

        $rows = [];

        foreach ($this->periods() as $period) {
            $key = $period['start'] . '-' . $period['end'];
            $time = $now?->format('H:i');

            $rows[] = [
                'label' => $period['label'],
                'type' => $period['type'],
                'current' => $time !== null && $time >= $period['start'] && $time < $period['end'],
                'cells' => $period['type'] === 'break'
                    ? []
                    : array_map(fn($col) => $cells[$key][$col] ?? [], $columnKeys),
            ];
        }

        return ['rows' => $rows, 'unmatched' => $unmatched];
    }

    /** Semua kelas aktif: satu blok jadwal mingguan per kelas (jumlah query tetap). */
    public function forAllClasses(?CarbonInterface $now = null): array
    {
        $days = $this->days();
        $isoDays = array_column($days, 'iso');
        $classes = $this->classes();

        $byClass = Schedule::query()
            ->active()
            ->whereIn('class_id', $classes->pluck('id'))
            ->with(['teacher', 'subject', 'schoolClass'])
            ->get()
            ->groupBy('class_id');

        $monitoringRows = $now !== null && in_array($now->dayOfWeekIso, $isoDays, true)
            ? app(MonitoringService::class)->sessionsFor($now, $now)
            ->keyBy(fn(MonitoringRow $row) => $row->schedule->id)
            : collect();

        $blocks = [];
        $unmatched = [];

        foreach ($classes as $class) {
            $grid = $this->build($byClass->get($class->id, collect()), $isoDays, fn(Schedule $s) => $s->day_of_week, $now, $monitoringRows);

            $unmatched = array_merge($unmatched, $grid['unmatched']);
            $blocks[] = ['class' => $class, 'rows' => $grid['rows']];
        }

        return [
            'blocks' => $blocks,
            'days' => $days,
            'todayIndex' => $this->todayIndex($isoDays, $now),
            'unmatched' => $unmatched,
        ];
    }

    private function todayIndex(array $isoDays, ?CarbonInterface $now): ?int
    {
        if ($now === null) {
            return null;
        }

        $index = array_search($now->dayOfWeekIso, $isoDays, true);

        return $index === false ? null : $index;
    }
}
