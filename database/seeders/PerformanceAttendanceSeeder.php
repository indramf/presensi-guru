<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PerformanceAttendanceSeeder extends Seeder
{
    /**
     * Menyiapkan 20 jadwal sintetis untuk mengukur aksi recordAttendance.
     * Jalankan hanya pada database pengujian:
     * php artisan db:seed --class=PerformanceAttendanceSeeder
     */
    public function run(): void
    {
        $teacherId = 17;
        $expectedUserId = 18;
        $dayOfWeek = 4; // Kamis (ISO-8601)
        $now = now('Asia/Jakarta');

        DB::transaction(function () use ($teacherId, $expectedUserId, $dayOfWeek, $now): void {
            $teacher = DB::table('teachers')->where('id', $teacherId)->first();

            if (! $teacher) {
                throw new RuntimeException("Guru dengan ID {$teacherId} tidak ditemukan.");
            }

            if ((int) $teacher->user_id !== $expectedUserId) {
                throw new RuntimeException("User ID guru {$teacherId} bukan {$expectedUserId}; seeder dibatalkan.");
            }

            if (! (bool) $teacher->is_active) {
                throw new RuntimeException("Guru dengan ID {$teacherId} tidak aktif; seeder dibatalkan.");
            }

            $classIds = DB::table('classes')
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(20)
                ->pluck('id')
                ->all();

            if (count($classIds) < 20) {
                throw new RuntimeException('Dibutuhkan minimal 20 kelas aktif yang sudah ada di database.');
            }

            $subjectIds = [];

            for ($i = 1; $i <= 5; $i++) {
                $subjectName = sprintf('QA Attendance Performance %02d', $i);

                DB::table('subjects')->updateOrInsert(
                    ['subject_name' => $subjectName],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );

                $subjectIds[] = DB::table('subjects')
                    ->where('subject_name', $subjectName)
                    ->value('id');
            }

            // Jadwal 60 menit: 12:30-13:30, 12:31-13:31, ... 12:49-13:49.
            for ($i = 0; $i < 20; $i++) {
                $start = $now->copy()->setTime(12, 30, 0)->addMinutes($i);
                $end = $start->copy()->addHour();

                DB::table('schedules')->updateOrInsert(
                    [
                        'teacher_id' => $teacherId,
                        'class_id' => $classIds[$i],
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $start->format('H:i:s'),
                    ],
                    [
                        'subject_id' => $subjectIds[$i % count($subjectIds)],
                        'type' => 'lesson',
                        'activity_name' => null,
                        'end_time' => $end->format('H:i:s'),
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        });

        $this->command?->info(
            'Selesai: 20 jadwal sintetis untuk teacher_id=17 (user_id=18), menggunakan 20 kelas yang sudah ada, hari Kamis, mulai pukul 12:30.'
        );
        $this->command?->warn(
            'Jalankan hanya pada database pengujian. Setiap presensi akan membuat data attendance dan audit log.'
        );
    }
}
