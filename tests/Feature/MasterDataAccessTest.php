<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MasterDataAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_fr03_guru_is_forbidden_from_admin_resources(): void
    {
        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();

        foreach (['/admin/teachers', '/admin/users', '/admin/schedules'] as $url) {
            $this->actingAs($guru)->get($url)->assertForbidden();
        }
    }

    public function test_fr03_piket_is_forbidden_from_master_data(): void
    {
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();

        foreach (['/admin/teachers', '/admin/users', '/admin/schedules'] as $url) {
            $this->actingAs($piket)->get($url)->assertForbidden();
        }
    }

    public function test_admin_can_open_master_data_pages(): void
    {
        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();

        foreach (['/admin/teachers', '/admin/users', '/admin/schedules'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
