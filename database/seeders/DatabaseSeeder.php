<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        if (app()->environment('testing')) {
            $this->call([
                DemoDataSeeder::class,
            ]);

            return;
        }

        $this->call([
            RealSchoolScheduleSeeder::class,
            UserSeeder::class,
        ]);
    }
}
