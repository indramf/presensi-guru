<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = config('initial_accounts.admin_password');
        $guruPassword = config('initial_accounts.guru_password');
        $piketPassword = config('initial_accounts.piket_password');

        foreach ([
            'ADMIN_INITIAL_PASSWORD' => $adminPassword,
            'GURU_INITIAL_PASSWORD' => $guruPassword,
            'PIKET_INITIAL_PASSWORD' => $piketPassword,
        ] as $key => $password) {
            if (! is_string($password) || strlen($password) < 8) {
                throw new RuntimeException(
                    "{$key} wajib diisi dan minimal 12 karakter."
                );
            }
        }

        $adminEmail = config('initial_accounts.admin_email');

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('ADMIN_EMAIL tidak valid.');
        }

        DB::transaction(function () use (
            $adminEmail,
            $adminPassword,
            $guruPassword,
            $piketPassword
        ): void {
            $this->createAccount(
                name: 'Administrator',
                username: 'admin',
                email: $adminEmail,
                password: $adminPassword,
                role: UserRole::Administrator->value,
            );

            for ($number = 1; $number <= 24; $number++) {
                $code = sprintf('RS%03d', $number);

                $teacher = Teacher::where('teacher_code', $code)->first();

                if (! $teacher) {
                    throw new RuntimeException(
                        "Data guru {$code} tidak ditemukan."
                    );
                }

                $user = $this->createAccount(
                    name: $teacher->full_name,
                    username: strtolower($code),
                    email: strtolower($code).'@presensiguru.local',
                    password: $guruPassword,
                    role: UserRole::Guru->value,
                );

                if ($teacher->user_id !== null && $teacher->user_id !== $user->id) {
                    throw new RuntimeException(
                        "Guru {$code} sudah terhubung ke akun lain."
                    );
                }

                $teacher->update(['user_id' => $user->id]);
            }

            for ($number = 1; $number <= 2; $number++) {
                $this->createAccount(
                    name: "Guru Piket {$number}",
                    username: "piket{$number}",
                    email: "piket{$number}@presensiguru.local",
                    password: $piketPassword,
                    role: UserRole::GuruPiket->value,
                );
            }
        });

        $this->command?->info('Berhasil menyiapkan 27 akun pengguna.');
    }

    private function createAccount(
        string $name,
        string $username,
        string $email,
        string $password,
        string $role,
    ): User {
        $byUsername = User::where('username', $username)->first();
        $byEmail = User::where('email', $email)->first();

        if ($byUsername && $byEmail && $byUsername->id !== $byEmail->id) {
            throw new RuntimeException(
                "Username atau email {$username} sudah digunakan akun lain."
            );
        }

        $user = $byUsername ?? $byEmail;

        if ($user) {
            if ($user->username !== $username || $user->email !== $email) {
                throw new RuntimeException(
                    "Identitas akun {$username} tidak cocok dengan data seeder."
                );
            }

            $user->update(['name' => $name]);
        } else {
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);
        }

        $user->syncRoles([$role]);

        return $user;
    }
}
