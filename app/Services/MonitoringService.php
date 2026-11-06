<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Enums\MonitoringStatus;
use App\Models\Attendance;
use App\Models\ClassReport;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MonitoringService
{
    /**
     * Semua sesi pada tanggal tertentu beserta statusnya.
     * Jumlah query tetap (tidak bergantung jumlah sesi) demi NFR-01.
     *
     * @return Collection<int, MonitoringRow>
     */
    public function sessionsFor(?CarbonInterface $date = null, ?CarbonInterface $now = null): Collection
    {
        $now ??= now();
        $date ??= $now;
        $day = $date->toDateString();

        $schedules = Schedule::query()
            ->active()
            ->lessons()
            ->where('day_of_week', $date->dayOfWeekIso)
            ->with(['teacher', 'schoolClass', 'subject'])
            ->orderBy('start_time')
            ->get();

        $ids = $schedules->pluck('id');

        $attendances = Attendance::query()
            ->whereDate('attendance_date', $day)
            ->whereIn('schedule_id', $ids)
            ->get()
            ->keyBy('schedule_id');

        // Laporan terbaru per sesi (id terbesar).
        $reports = ClassReport::query()
            ->whereDate('report_date', $day)
            ->whereIn('schedule_id', $ids)
            ->orderByDesc('id')
            ->get()
            ->unique('schedule_id')
            ->keyBy('schedule_id');

        return $schedules->map(function (Schedule $schedule) use ($attendances, $reports, $date, $now) {
            $attendance = $attendances->get($schedule->id);
            $report = $reports->get($schedule->id);
            $status = $this->statusFor($schedule, $attendance, $date, $now);

            return new MonitoringRow($schedule, $attendance, $report, $status, $this->needsAction($status, $report));
        })->values();
    }

    /** @return array<string,int> jumlah per status ditambah 'needs_action' */
    public function summary(Collection $rows): array
    {
        $counts = [];

        foreach (MonitoringStatus::cases() as $case) {
            $counts[$case->value] = 0;
        }

        foreach ($rows as $row) {
            $counts[$row->status->value]++;
        }

        $counts['needs_action'] = $rows->where('needsAction', true)->count();

        return $counts;
    }

    private function statusFor(Schedule $schedule, ?Attendance $attendance, CarbonInterface $date, CarbonInterface $now): MonitoringStatus
    {
        if ($attendance !== null) {
            return match ($attendance->status) {
                AttendanceStatus::Hadir => MonitoringStatus::Hadir,
                AttendanceStatus::Terlambat => MonitoringStatus::Terlambat,
                AttendanceStatus::Izin => MonitoringStatus::Izin,
                AttendanceStatus::TidakHadir => MonitoringStatus::TidakHadir,
            };
        }

        $start = Carbon::parse($date->toDateString().' '.$schedule->start_time, $date->getTimezone());
        $threshold = $start->copy()->addMinutes((int) config('monitoring.unconfirmed_after_minutes'));

        // Tanpa catatan: tidak pernah dianggap "tidak hadir".
        return $now->lt($threshold) ? MonitoringStatus::Menunggu : MonitoringStatus::BelumTerkonfirmasi;
    }

    private function needsAction(MonitoringStatus $status, ?ClassReport $report): bool
    {
        if ($report !== null) {
            return $report->report_status !== ClassReportStatus::GuruHadir
                && $report->follow_up_status !== FollowUpStatus::Selesai;
        }

        return in_array($status, [MonitoringStatus::BelumTerkonfirmasi, MonitoringStatus::TidakHadir], true);
    }
}
