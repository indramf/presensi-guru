<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Models\Attendance;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class TodayScenarioSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();
        $now = now();
        $today = $now->toDateString();

        $started = Schedule::active()
            ->where('day_of_week', $now->dayOfWeekIso)
            ->orderBy('start_time')
            ->get()
            ->filter(fn ($s) => "{$today} {$s->start_time}" <= $now->format('Y-m-d H:i:s'))
            ->values();

        foreach ($started as $i => $schedule) {
            $pattern = $i % 6;   // 0 hadir, 1 terlambat, 2 izin, 3 belum terkonfirmasi, 4 tidak hadir, 5 belum + laporan

            if (in_array($pattern, [0, 1, 2, 4], true)) {
                Attendance::updateOrCreate(
                    ['schedule_id' => $schedule->id, 'attendance_date' => $today],
                    [
                        'teacher_id' => $schedule->teacher_id,
                        'status' => [0 => AttendanceStatus::Hadir, 1 => AttendanceStatus::Terlambat,
                            2 => AttendanceStatus::Izin, 4 => AttendanceStatus::TidakHadir][$pattern],
                        'recorded_by' => $admin->id,
                        'recorded_at' => $now,
                    ],
                );
            }

            if ($pattern === 5) {
                ClassReport::updateOrCreate(
                    ['schedule_id' => $schedule->id, 'report_date' => $today],
                    [
                        'reported_by' => $piket->id,
                        'report_status' => ClassReportStatus::KelasDiberiTugas,
                        'description' => 'Siswa diberi tugas oleh guru piket',
                        'follow_up_status' => FollowUpStatus::Proses,
                    ],
                );
            }
        }
    }
}
