<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PerfSeed extends Command
{
    protected $signature = 'perf:seed {--classes=30 : Jumlah kelas} {--slots=8 : Jam pelajaran per hari}';

    protected $description = 'Membuat data jadwal berukuran besar untuk uji kinerja (hanya lingkungan uji)';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Ditolak di environment production.');

            return self::FAILURE;
        }

        $classCount = max(1, (int) $this->option('classes'));
        $slots = max(1, (int) $this->option('slots'));
        $teacherCount = $classCount + 10;   // guru lebih banyak dari kelas: tidak ada bentrok guru

        $subject = Subject::firstOrCreate(['subject_name' => 'Mapel Uji Kinerja']);

        $classes = collect(range(1, $classCount))->map(fn (int $i) => SchoolClass::firstOrCreate(
            ['class_name' => "PERF {$i}", 'academic_year' => '2026/2027'],
            ['is_active' => true],
        ))->values();

        if (Schedule::where('class_id', $classes->first()->id)->exists()) {
            $this->warn('Data kinerja sudah ada. Jalankan migrate:fresh --seed dulu bila ingin mengulang.');

            return self::SUCCESS;
        }

        $teachers = collect(range(1, $teacherCount))->map(fn (int $i) => Teacher::firstOrCreate(
            ['teacher_code' => sprintf('P%03d', $i)],
            ['full_name' => "Guru Kinerja {$i}", 'is_active' => true],
        ))->values();

        $rows = [];

        foreach (range(1, 5) as $day) {
            foreach (range(0, $slots - 1) as $s) {
                $start = Carbon::createFromTime(7, 0)->addMinutes($s * 45);

                foreach ($classes as $c => $class) {
                    $rows[] = [
                        'teacher_id' => $teachers[($s + $c) % $teachers->count()]->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'day_of_week' => $day,
                        'start_time' => $start->format('H:i:s'),
                        'end_time' => $start->copy()->addMinutes(45)->format('H:i:s'),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Schedule::insert($chunk);
        }

        $this->info(sprintf(
            'Dibuat: %d kelas, %d guru, %d jadwal (%d sesi per hari kerja).',
            $classCount, $teacherCount, count($rows), $classCount * $slots,
        ));

        return self::SUCCESS;
    }
}
