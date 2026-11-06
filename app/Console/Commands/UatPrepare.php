<?php

namespace App\Console\Commands;

use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\ClassReportService;
use Illuminate\Console\Command;

class UatPrepare extends Command
{
    protected $signature = 'uat:prepare {--fresh : Reset database lebih dulu} {--force : Izinkan di environment production}';

    protected $description = 'Menyiapkan data UAT: enam sesi hari ini di sekitar waktu sekarang dengan berbagai status';

    public function handle(AttendanceService $attendance, ClassReportService $reports): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Perintah ini mengubah data. Ditolak di environment production (gunakan --force bila memang disengaja).');

            return self::FAILURE;
        }

        $now = now();

        // Sesi dibuat dari 65 menit lalu sampai 60 menit ke depan; hindari pergantian hari.
        if (
            $now->copy()->subMinutes(65)->toDateString() !== $now->toDateString()
            || $now->copy()->addMinutes(60)->toDateString() !== $now->toDateString()
        ) {
            $this->error('Terlalu dekat dengan pergantian hari. Jalankan lagi pada jam lain.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--force' => true]);
            @unlink(config('audit.checkpoint_path'));   // checkpoint lama tidak cocok dengan database baru
        }

        $this->call('db:seed', ['--force' => true]);    // peran dan akun uji; aman dijalankan berulang

        // Hanya enam sesi UAT yang aktif, supaya dashboard tidak ramai.
        Schedule::query()->update(['is_active' => false]);

        $subject = Subject::firstOrFail();
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();

        $make = function (int $n, int $startOffset, int $endOffset, string $teacherCode) use ($now, $subject): array {
            $class = SchoolClass::updateOrCreate(
                ['class_name' => "UAT {$n}", 'academic_year' => '2026/2027'],
                ['is_active' => true],
            );
            $teacher = Teacher::where('teacher_code', $teacherCode)->firstOrFail();
            $start = $now->copy()->addMinutes($startOffset);

            $schedule = Schedule::create([
                'type' => 'lesson',
                'activity_name' => null,
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'day_of_week' => $now->dayOfWeekIso,
                'start_time' => $start->format('H:i:s'),
                'end_time' => $now->copy()->addMinutes($endOffset)->format('H:i:s'),
                'is_active' => true,
            ]);

            return [$schedule, $start, $teacher];
        };

        // Sesi 1: sudah berlalu, guru mengisi tepat waktu (Hadir).
        [$s1, $start1, $t1] = $make(1, -60, -15, 'G001');
        $attendance->record($t1->user, $s1, $start1->copy()->addMinutes(3));

        // Sesi 2: sudah berlalu, guru mengisi terlambat (Terlambat).
        [$s2, $start2, $t2] = $make(2, -45, -2, 'G002');
        $attendance->record($t2->user, $s2, $start2->copy()->addMinutes(12));

        // Sesi 3: baru dimulai, belum diisi (Menunggu). Guru 3 dapat mengisinya saat UAT.
        $make(3, -3, 42, 'G003');

        // Sesi 4: berjalan, tanpa presensi dan tanpa laporan (Belum terkonfirmasi, perlu tindakan).
        $make(4, -25, 20, 'G004');

        // Sesi 5: berjalan, tanpa presensi tetapi sudah dilaporkan guru piket (tindak lanjut Proses).
        [$s5] = $make(5, -20, 25, 'G005');
        $report = $reports->create($piket, $s5, ClassReportStatus::KelasDiberiTugas, 'Siswa diberi tugas oleh guru piket', $now);
        $reports->updateFollowUp($piket, $report, FollowUpStatus::Proses, 'Guru pengganti sedang dihubungi');

        // Sesi 6: akan dimulai 10 menit lagi (Menunggu). Jendela presensi guru 1 sudah terbuka.
        $make(6, 10, 55, 'G001');

        $this->info('Data UAT siap untuk '.$now->format('d M Y H:i').':');
        $this->table(['Sesi', 'Kelas', 'Guru', 'Keadaan'], [
            [1, 'UAT 1', 'guru1', 'Hadir (sudah berlalu)'],
            [2, 'UAT 2', 'guru2', 'Terlambat (sudah berlalu)'],
            [3, 'UAT 3', 'guru3', 'Menunggu, siap diisi'],
            [4, 'UAT 4', 'guru4', 'Belum terkonfirmasi, perlu tindakan'],
            [5, 'UAT 5', 'guru5', 'Belum terkonfirmasi, sudah dilaporkan (Proses)'],
            [6, 'UAT 6', 'guru1', 'Akan dimulai, dapat diisi'],
        ]);
        $this->line('Akun: admin@presensi.test, piket@presensi.test, guru1 sampai guru5 @presensi.test (kata sandi: password).');

        return self::SUCCESS;
    }
}
