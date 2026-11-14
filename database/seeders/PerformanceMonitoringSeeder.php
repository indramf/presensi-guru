<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $academicYear = '2026/2027';

            // Buat 24 kelas sintetis.
            $classIds = [];

            for ($i = 1; $i <= 24; $i++) {
                $name = sprintf('QA-PERF-%02d', $i);

                DB::table('classes')->updateOrInsert(
                    [
                        'class_name' => $name,
                        'academic_year' => $academicYear,
                    ],
                    [
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $classIds[] = DB::table('classes')
                    ->where('class_name', $name)
                    ->where('academic_year', $academicYear)
                    ->value('id');
            }

            // Buat 24 guru sintetis tanpa akun pengguna.
            $teacherIds = [];

            for ($i = 1; $i <= 24; $i++) {
                $code = sprintf('QA-PERF-%03d', $i);

                DB::table('teachers')->updateOrInsert(
                    ['teacher_code' => $code],
                    [
                        'user_id' => null,
                        'full_name' => sprintf('QA Performance Teacher %02d', $i),
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $teacherIds[] = DB::table('teachers')
                    ->where('teacher_code', $code)
                    ->value('id');
            }

            // Buat 5 mata pelajaran sintetis.
            $subjectIds = [];

            for ($i = 1; $i <= 5; $i++) {
                $name = sprintf('QA Performance Subject %02d', $i);

                DB::table('subjects')->updateOrInsert(
                    ['subject_name' => $name],
                    [
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $subjectIds[] = DB::table('subjects')
                    ->where('subject_name', $name)
                    ->value('id');
            }

            // Hapus jadwal sintetis sebelumnya agar seeder aman dijalankan ulang.
            DB::table('schedules')
                ->whereIn('class_id', $classIds)
                ->where('day_of_week', 5)
                ->delete();

            // Lima sesi dalam rentang KBM 07.30 – 15.00
            $periods = [
                ['07:30:00', '08:10:00'],
                ['09:00:00', '09:40:00'],
                ['10:30:00', '11:10:00'],
                ['12:30:00', '13:10:00'],
                ['14:00:00', '14:40:00'],
            ];

            $schedules = [];

            foreach ($periods as $periodIndex => [$start, $end]) {
                foreach ($classIds as $classIndex => $classId) {
                    $schedules[] = [
                        'teacher_id' => $teacherIds[$classIndex],
                        'class_id' => $classId,
                        'subject_id' => $subjectIds[
                            ($classIndex + $periodIndex) % 5
                        ],
                        'type' => 'lesson',
                        'activity_name' => null,
                        'day_of_week' => 5,
                        'start_time' => $start,
                        'end_time' => $end,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('schedules')->insert($schedules);
        });
    }
}
