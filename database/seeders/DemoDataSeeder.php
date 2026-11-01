<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->makeUser('Admin Uji', 'admin', 'admin@presensi.test', UserRole::Administrator);
        $this->makeUser('Guru Piket Uji', 'piket', 'piket@presensi.test', UserRole::GuruPiket);

        $names = ['Budi Santoso', 'Sri Lestari', 'Agus Setiawan', 'Dewi Anggraini', 'Hendra Gunawan'];
        $teachers = [];
        foreach ($names as $i => $name) {
            $n = $i + 1;
            $user = $this->makeUser($name, "guru{$n}", "guru{$n}@presensi.test", UserRole::Guru);
            $teachers[] = Teacher::updateOrCreate(
                ['teacher_code' => sprintf('G%03d', $n)],
                ['user_id' => $user->id, 'full_name' => $name, 'is_active' => true],
            );
        }

        $classes = collect(['X RPL 1', 'X RPL 2', 'XI TKJ 1'])->map(
            fn ($name) => SchoolClass::updateOrCreate(
                ['class_name' => $name, 'academic_year' => '2026/2027'],
                ['is_active' => true],
            )
        )->all();

        $subjects = collect([
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Pemrograman Dasar',
            'Jaringan Dasar',
            'Pendidikan Pancasila',
        ])->map(
            fn ($name) => Subject::updateOrCreate(['subject_name' => $name], ['is_active' => true])
        )->all();

        // Jam DUMMY, belum dikonfirmasi sekolah.
        $slots = [
            ['07:00:00', '07:45:00'],
            ['07:45:00', '08:30:00'],
            ['08:30:00', '09:15:00'],
            ['09:30:00', '10:15:00'],
            ['10:15:00', '11:00:00'],
            ['11:00:00', '11:45:00'],
        ];

        foreach (range(1, 5) as $day) {
            foreach ($slots as $s => [$start, $end]) {
                foreach ($classes as $c => $class) {
                    Schedule::updateOrCreate(
                        ['class_id' => $class->id, 'day_of_week' => $day, 'start_time' => $start],
                        [
                            'teacher_id' => $teachers[($s + $c) % count($teachers)]->id,
                            'subject_id' => $subjects[($s + $c + $day) % count($subjects)]->id,
                            'end_time' => $end,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }

    private function makeUser(string $name, string $username, string $email, UserRole $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'username' => $username, 'password' => 'password', 'is_active' => true],
        );
        $user->syncRoles([$role->value]);

        return $user;
    }
}
