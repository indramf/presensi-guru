<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.deactivate',
            'users.reset_password',
            'teachers.view',
            'teachers.create',
            'teachers.update',
            'teachers.deactivate',
            'classes.view',
            'classes.create',
            'classes.update',
            'subjects.view',
            'classes.deactivate',
            'subjects.create',
            'subjects.update',
            'subjects.deactivate',
            'schedules.view',
            'schedules.create',
            'schedules.update',
            'schedules.deactivate',
            'attendance.view_own',
            'attendance.view_all',
            'attendance.record',
            'attendance.change_status',
            'attendance.view_all',
            'attendance.change_status',
            'attendance.delete',
            'dashboard.view',
            'class_reports.view',
            'class_reports.create',
            'class_reports.update',
            'audit_logs.view',
            'audit_logs.verify',
            'security_alerts.view',
            'security_alerts.review',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $map = [
            UserRole::Administrator->value => [
                'users.view',
                'users.create',
                'users.update',
                'users.deactivate',
                'users.reset_password',
                'teachers.view',
                'teachers.create',
                'teachers.update',
                'teachers.deactivate',
                'classes.view',
                'classes.create',
                'classes.update',
                'classes.deactivate',
                'subjects.view',
                'subjects.create',
                'subjects.update',
                'subjects.deactivate',
                'schedules.view',
                'schedules.create',
                'schedules.update',
                'schedules.deactivate',
                'attendance.view_all',
                'attendance.change_status',
                'attendance.view_all',
                'attendance.change_status',
                'attendance.delete',
                'dashboard.view',
                'class_reports.view',
                'class_reports.update',
                'audit_logs.view',
                'audit_logs.verify',
                'security_alerts.view',
                'security_alerts.review',
            ],
            UserRole::Guru->value => [
                'attendance.view_own',
                'attendance.record',
            ],
            UserRole::GuruPiket->value => [
                'dashboard.view',
                'attendance.view_all',
                'attendance.change_status',
                'class_reports.view',
                'class_reports.create',
                'class_reports.update',
            ],
        ];

        foreach ($map as $role => $names) {
            Role::findOrCreate($role, 'web')->syncPermissions($names);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
