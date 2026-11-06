<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ScheduleConflictChecker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealSchoolScheduleSeeder extends Seeder
{
    private const ACADEMIC_YEAR = '2026/2027';

    private const TEACHERS = [
        'Alis Tati Hartati, S.Pd',
        'Ani Nuriyani, S.Pd',
        'Anisa Amalia Wahyuni',
        'Arfida Nuha Piinata',
        'Devi Fauziah, S.Pd',
        'Eli Herlina Khoirunnisa, S.Pd.I',
        'Etih Pandu Windari, M.Pd',
        'Halimah, S.Ag',
        'Iin Parlina, S.Pd',
        'Ismia Hazar Ridwan, S.E',
        'Laily',
        'Lisna Mardiana Solihat, S.Ag',
        'Maya Mahdiyah, M.Pd',
        'Mina Fakhriyati Masykur, S.Pd',
        'Nurotul Insani, S.Ag',
        'Nurfitri',
        'Rosi Rahmayanti, S.Ak',
        'Siti Adawiyah, S.Ak',
        'Tika Rahmawati, S.M',
        'Vita Fatimah, A.Md',
        'Widia Nurkamal, S.Ag',
        'Yusfiah Latifah, S.Kom',
        'Yeni Fadillah',
        'Yuli Sri Wulandari, S.Pd.I',
    ];

    private const CLASSES = [
        'IV TKJT',
        'V TKJT',
        'IV AKL',
        'V AKL',
        'VI AKL',
    ];

    private const SUBJECTS = [
        'Administrasi Sistem Jaringan',
        'Akuntansi Dasar',
        'Akuntansi Keuangan',
        'Akuntansi Perusahaan Dagang',
        'Akuntansi Perusahaan dan Manufaktur',
        'Amtsilati',
        'Bahasa Inggris',
        'Bahasa Indonesia',
        'Desain Grafis',
        'Digital Marketing',
        'Ekonomi Bisnis',
        'Fiqih',
        'Hadits',
        'Informatika',
        'Jaringan Dasar',
        'Keamanan Jaringan',
        'Komputer Akuntansi',
        'Kreatif dan Inovasi Kewirausahaan',
        'Matematika',
        'Pemrograman Web',
        'Pendidikan Pancasila',
        'Perbankan Dasar',
        'Perencanaan dan Pengalamatan Jaringan',
        'Projek Ilmu Pengetahuan Alam dan Sosial',
        'Sejarah',
        'SKI',
        'Spreadsheet',
        'Sistem Komputer',
        'Tahsin Al-Qur’an',
        'Tasawuf',
        'Teknologi Jaringan Kabel dan Nirkabel',
        'Tauhid',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            if (Schedule::exists()) {
                throw new \RuntimeException(
                    'Tabel schedules tidak kosong. Seeder dibatalkan agar jadwal yang sudah ada tidak tertimpa.'
                );
            }

            $this->seedTeachers();
            $this->seedClasses();
            $this->seedSubjects();
            $this->seedSchedules();
        });
    }

    private function seedTeachers(): void
    {
        foreach (self::TEACHERS as $index => $name) {
            Teacher::updateOrCreate(
                ['teacher_code' => sprintf('RS%03d', $index + 1)],
                [
                    'user_id' => null,
                    'full_name' => $name,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedClasses(): void
    {
        foreach (self::CLASSES as $className) {
            SchoolClass::updateOrCreate(
                [
                    'class_name' => $className,
                    'academic_year' => self::ACADEMIC_YEAR,
                ],
                ['is_active' => true],
            );
        }
    }

    private function seedSubjects(): void
    {
        foreach (self::SUBJECTS as $subjectName) {
            Subject::firstOrCreate(
                ['subject_name' => $subjectName],
                ['is_active' => true],
            );
        }
    }

    private function seedSchedules(): void
    {
        $checker = app(ScheduleConflictChecker::class);

        foreach ($this->scheduleData() as $item) {
            $class = SchoolClass::where('class_name', $item['class'])
                ->where('academic_year', self::ACADEMIC_YEAR)
                ->firstOrFail();

            if ($item['type'] === 'activity') {
                $teacherId = null;
                $subjectId = null;
            } else {
                $teacherId = Teacher::where('full_name', $item['teacher'])
                    ->firstOrFail()
                    ->id;

                $subjectId = Subject::where('subject_name', $item['subject'])
                    ->firstOrFail()
                    ->id;
            }

            $conflicts = $checker->check(
                $teacherId,
                $class->id,
                $item['day'],
                $item['start'],
                $item['end'],
            );

            if ($conflicts !== []) {
                throw new \RuntimeException(sprintf(
                    'Konflik jadwal %s, hari %s, %s-%s: %s',
                    $item['class'],
                    $item['day'],
                    $item['start'],
                    $item['end'],
                    implode(' ', $conflicts),
                ));
            }

            Schedule::create([
                'teacher_id' => $teacherId,
                'class_id' => $class->id,
                'subject_id' => $subjectId,
                'type' => $item['type'],
                'activity_name' => $item['activity_name'] ?? null,
                'day_of_week' => $item['day'],
                'start_time' => $item['start'],
                'end_time' => $item['end'],
                'is_active' => true,
            ]);
        }
    }

    private function scheduleData(): array
    {
        return [
            // ==========================================
            // IV TKJT
            // ==========================================

            // Sabtu
            [
                'class' => 'IV TKJT',
                'day' => 6,
                'type' => 'activity',
                'activity_name' => 'Upacara Bendera',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Desain Grafis',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '08:10',
                'end' => '10:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Desain Grafis',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Pemrograman Web',
                'teacher' => 'Arfida Nuha Piinata',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Minggu
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'activity',
                'activity_name' => 'Pengajian Ahad',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
                'start' => '08:10',
                'end' => '09:30',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Jaringan Dasar',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Jaringan Dasar',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Yeni Fadillah',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Senin
            [
                'class' => 'IV TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Pendidikan Pancasila',
                'teacher' => 'Alis Tati Hartati, S.Pd',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Jaringan Dasar',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Jaringan Dasar',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Projek Ilmu Pengetahuan Alam dan Sosial',
                'teacher' => 'Mina Fakhriyati Masykur, S.Pd',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Projek Ilmu Pengetahuan Alam dan Sosial',
                'teacher' => 'Mina Fakhriyati Masykur, S.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Tasawuf',
                'teacher' => 'Laily',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Selasa
            [
                'class' => 'IV TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Informatika',
                'teacher' => 'Tika Rahmawati, S.M',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Projek Ilmu Pengetahuan Alam dan Sosial',
                'teacher' => 'Mina Fakhriyati Masykur, S.Pd',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Iin Parlina, S.Pd',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Iin Parlina, S.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Pemrograman Web',
                'teacher' => 'Arfida Nuha Piinata',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Rabu
            [
                'class' => 'IV TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
                'start' => '07:30',
                'end' => '09:30',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Sistem Komputer',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Sistem Komputer',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Yeni Fadillah',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Kamis
            [
                'class' => 'IV TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'SKI',
                'teacher' => 'Yuli Sri Wulandari, S.Pd.I',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Yeni Fadillah',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // ==========================================
            // V TKJT
            // ==========================================

            // Sabtu
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'activity',
                'activity_name' => 'Upacara Bendera',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'SKI',
                'teacher' => 'Yuli Sri Wulandari, S.Pd.I',
                'start' => '08:10',
                'end' => '09:30',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Keamanan Jaringan',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '13:50',
                'end' => '14:25',
            ],
            [
                'class' => 'V TKJT',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Desain Grafis',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '14:25',
                'end' => '15:00',
            ],

            // Minggu
            [
                'class' => 'V TKJT',
                'day' => 7,
                'type' => 'activity',
                'activity_name' => 'Pengajian Ahad',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
                'start' => '08:10',
                'end' => '10:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Devi Fauziah, S.Pd',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Desain Grafis',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Devi Fauziah, S.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Teknologi Jaringan Kabel dan Nirkabel',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Senin
            [
                'class' => 'V TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '07:30',
                'end' => '09:30',
            ],
            [
                'class' => 'V TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Administrasi Sistem Jaringan',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Administrasi Sistem Jaringan',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Selasa
            [
                'class' => 'V TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Pendidikan Pancasila',
                'teacher' => 'Alis Tati Hartati, S.Pd',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Administrasi Sistem Jaringan',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Administrasi Sistem Jaringan',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Tasawuf',
                'teacher' => 'Laily',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Rabu
            [
                'class' => 'V TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Pemrograman Web',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Kreatif dan Inovasi Kewirausahaan',
                'teacher' => 'Siti Adawiyah, S.Ak',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Perencanaan dan Pengalamatan Jaringan',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '13:15',
                'end' => '15:00',
            ],

            // Kamis
            [
                'class' => 'V TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Pemrograman Web',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Teknologi Jaringan Kabel dan Nirkabel',
                'teacher' => 'Yusfiah Latifah, S.Kom',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'V TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Keamanan Jaringan',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Perencanaan dan Pengalamatan Jaringan',
                'teacher' => 'Vita Fatimah, A.Md',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V TKJT',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // ==========================================
            // IV AKL
            // ==========================================

            // Sabtu
            [
                'class' => 'IV AKL',
                'day' => 6,
                'type' => 'activity',
                'activity_name' => 'Upacara Bendera',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Spreadsheet',
                'teacher' => 'Tika Rahmawati, S.M',
                'start' => '08:10',
                'end' => '10:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Ekonomi Bisnis',
                'teacher' => 'Tika Rahmawati, S.M',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Ekonomi Bisnis',
                'teacher' => 'Tika Rahmawati, S.M',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Yeni Fadillah',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Minggu
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'activity',
                'activity_name' => 'Pengajian Ahad',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
                'start' => '08:10',
                'end' => '09:30',
            ],
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Akuntansi Dasar',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Akuntansi Dasar',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Akuntansi Dasar',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Senin
            [
                'class' => 'IV AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Tasawuf',
                'teacher' => 'Laily',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Projek Ilmu Pengetahuan Alam dan Sosial',
                'teacher' => 'Mina Fakhriyati Masykur, S.Pd',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Yeni Fadillah',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Selasa
            [
                'class' => 'IV AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'SKI',
                'teacher' => 'Yuli Sri Wulandari, S.Pd.I',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Pendidikan Pancasila',
                'teacher' => 'Alis Tati Hartati, S.Pd',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Maya Mahdiyah, M.Pd',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Maya Mahdiyah, M.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Yeni Fadillah',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Rabu
            [
                'class' => 'IV AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Informatika',
                'teacher' => 'Tika Rahmawati, S.M',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Projek Ilmu Pengetahuan Alam dan Sosial',
                'teacher' => 'Mina Fakhriyati Masykur, S.Pd',
                'start' => '13:15',
                'end' => '15:00',
            ],

            // Kamis
            [
                'class' => 'IV AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Akuntansi Dasar',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '07:30',
                'end' => '08:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Perbankan Dasar',
                'teacher' => 'Siti Adawiyah, S.Ak',
                'start' => '08:50',
                'end' => '10:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Perbankan Dasar',
                'teacher' => 'Siti Adawiyah, S.Ak',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'IV AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'IV AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // ==========================================
            // V AKL
            // ==========================================

            // Sabtu
            [
                'class' => 'V AKL',
                'day' => 6,
                'type' => 'activity',
                'activity_name' => 'Upacara Bendera',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '08:10',
                'end' => '10:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 6,
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Minggu
            [
                'class' => 'V AKL',
                'day' => 7,
                'type' => 'activity',
                'activity_name' => 'Pengajian Ahad',
                'start' => '07:30',
                'end' => '08:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Komputer Akuntansi',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '08:10',
                'end' => '10:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 7,
                'type' => 'lesson',
                'subject' => 'Digital Marketing',
                'teacher' => 'Arfida Nuha Piinata',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Senin
            [
                'class' => 'V AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan dan Manufaktur',
                'teacher' => 'Ismia Hazar Ridwan, S.E',
                'start' => '07:30',
                'end' => '09:30',
            ],
            [
                'class' => 'V AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Kreatif dan Inovasi Kewirausahaan',
                'teacher' => 'Siti Adawiyah, S.Ak',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Kreatif dan Inovasi Kewirausahaan',
                'teacher' => 'Siti Adawiyah, S.Ak',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Pendidikan Pancasila',
                'teacher' => 'Alis Tati Hartati, S.Pd',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Pendidikan Pancasila',
                'teacher' => 'Alis Tati Hartati, S.Pd',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 1,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Selasa
            [
                'class' => 'V AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
                'start' => '07:30',
                'end' => '09:30',
            ],
            [
                'class' => 'V AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'SKI',
                'teacher' => 'Yuli Sri Wulandari, S.Pd.I',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'SKI',
                'teacher' => 'Yuli Sri Wulandari, S.Pd.I',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Tasawuf',
                'teacher' => 'Laily',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Tasawuf',
                'teacher' => 'Laily',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 2,
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // Rabu
            [
                'class' => 'V AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
                'start' => '07:30',
                'end' => '09:30',
            ],
            [
                'class' => 'V AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
                'start' => '10:30',
                'end' => '11:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 3,
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Iin Parlina, S.Pd',
                'start' => '13:15',
                'end' => '15:00',
            ],

            // Kamis
            [
                'class' => 'V AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Akuntansi Keuangan',
                'teacher' => 'Ismia Hazar Ridwan, S.E',
                'start' => '07:30',
                'end' => '09:30',
            ],
            [
                'class' => 'V AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
                'start' => '09:30',
                'end' => '10:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
                'start' => '10:30',
                'end' => '11:10',
            ],
            [
                'class' => 'V AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
                'start' => '11:10',
                'end' => '11:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
                'start' => '13:15',
                'end' => '13:50',
            ],
            [
                'class' => 'V AKL',
                'day' => 4,
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
                'start' => '13:50',
                'end' => '15:00',
            ],

            // ==========================================
            // VI AKL
            // ==========================================

            // Sabtu
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '07:30',
                'end' => '08:10',
                'type' => 'activity',
                'activity_name' => 'Upacara Bendera',
            ],
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '08:10',
                'end' => '09:30',
                'type' => 'lesson',
                'subject' => 'Tauhid',
                'teacher' => 'Lisna Mardiana Solihat, S.Ag',
            ],
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '09:30',
                'end' => '10:10',
                'type' => 'lesson',
                'subject' => 'Kreatif dan Inovasi Kewirausahaan',
                'teacher' => 'Siti Adawiyah, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '10:30',
                'end' => '11:10',
                'type' => 'lesson',
                'subject' => 'Kreatif dan Inovasi Kewirausahaan',
                'teacher' => 'Siti Adawiyah, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '11:10',
                'end' => '11:50',
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
            ],
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '13:15',
                'end' => '13:50',
                'type' => 'lesson',
                'subject' => 'Tahsin Al-Qur’an',
                'teacher' => 'Halimah, S.Ag',
            ],
            [
                'class' => 'VI AKL',
                'day' => 6,
                'start' => '13:50',
                'end' => '15:00',
                'type' => 'lesson',
                'subject' => 'Komputer Akuntansi',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
            ],

            // Minggu

            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '07:30',
                'end' => '08:10',
                'type' => 'activity',
                'activity_name' => 'Pengajian Ahad',
            ],
            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '08:10',
                'end' => '09:30',
                'type' => 'lesson',
                'subject' => 'Hadits',
                'teacher' => 'Nurotul Insani, S.Ag',
            ],
            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '09:30',
                'end' => '10:10',
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
            ],
            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '10:30',
                'end' => '11:10',
                'type' => 'lesson',
                'subject' => 'Sejarah',
                'teacher' => 'Anisa Amalia Wahyuni',
            ],
            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '11:10',
                'end' => '11:50',
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
            ],
            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '13:15',
                'end' => '13:50',
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
            ],
            [
                'class' => 'VI AKL',
                'day' => 7,
                'start' => '13:50',
                'end' => '15:00',
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
            ],

            // Senin

            [
                'class' => 'VI AKL',
                'day' => 1,
                'start' => '07:30',
                'end' => '10:10',
                'type' => 'lesson',
                'subject' => 'Komputer Akuntansi',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 1,
                'start' => '10:30',
                'end' => '11:50',
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 1,
                'start' => '13:15',
                'end' => '13:50',
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 1,
                'start' => '13:50',
                'end' => '15:00',
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan dan Manufaktur',
                'teacher' => 'Ismia Hazar Ridwan, S.E',
            ],

            // Selasa

            [
                'class' => 'VI AKL',
                'day' => 2,
                'start' => '07:30',
                'end' => '09:30',
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan dan Manufaktur',
                'teacher' => 'Ismia Hazar Ridwan, S.E',
            ],
            [
                'class' => 'VI AKL',
                'day' => 2,
                'start' => '09:30',
                'end' => '10:10',
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
            ],
            [
                'class' => 'VI AKL',
                'day' => 2,
                'start' => '10:30',
                'end' => '11:10',
                'type' => 'lesson',
                'subject' => 'Fiqih',
                'teacher' => 'Widia Nurkamal, S.Ag',
            ],
            [
                'class' => 'VI AKL',
                'day' => 2,
                'start' => '11:10',
                'end' => '11:50',
                'type' => 'lesson',
                'subject' => 'Bahasa Indonesia',
                'teacher' => 'Etih Pandu Windari, M.Pd',
            ],
            [
                'class' => 'VI AKL',
                'day' => 2,
                'start' => '13:15',
                'end' => '15:00',
                'type' => 'lesson',
                'subject' => 'Bahasa Inggris',
                'teacher' => 'Devi Fauziah, S.Pd',
            ],

            // Rabu

            [
                'class' => 'VI AKL',
                'day' => 3,
                'start' => '07:30',
                'end' => '08:50',
                'type' => 'lesson',
                'subject' => 'Tasawuf',
                'teacher' => 'Laily',
            ],
            [
                'class' => 'VI AKL',
                'day' => 3,
                'start' => '08:50',
                'end' => '10:10',
                'type' => 'lesson',
                'subject' => 'SKI',
                'teacher' => 'Eli Herlina Khoirunnisa, S.Pd.I',
            ],
            [
                'class' => 'VI AKL',
                'day' => 3,
                'start' => '10:30',
                'end' => '11:50',
                'type' => 'lesson',
                'subject' => 'Digital Marketing',
                'teacher' => 'Arfida Nuha Piinata',
            ],
            [
                'class' => 'VI AKL',
                'day' => 3,
                'start' => '13:15',
                'end' => '15:00',
                'type' => 'lesson',
                'subject' => 'Matematika',
                'teacher' => 'Ani Nuriyani, S.Pd',
            ],

            // Kamis

            [
                'class' => 'VI AKL',
                'day' => 4,
                'start' => '07:30',
                'end' => '09:30',
                'type' => 'lesson',
                'subject' => 'Amtsilati',
                'teacher' => 'Nurfitri',
            ],
            [
                'class' => 'VI AKL',
                'day' => 4,
                'start' => '09:30',
                'end' => '10:10',
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 4,
                'start' => '10:30',
                'end' => '11:50',
                'type' => 'lesson',
                'subject' => 'Akuntansi Perusahaan Dagang',
                'teacher' => 'Rosi Rahmayanti, S.Ak',
            ],
            [
                'class' => 'VI AKL',
                'day' => 4,
                'start' => '13:15',
                'end' => '15:00',
                'type' => 'lesson',
                'subject' => 'Pendidikan Pancasila',
                'teacher' => 'Alis Tati Hartati, S.Pd',
            ],
        ];
    }
}
