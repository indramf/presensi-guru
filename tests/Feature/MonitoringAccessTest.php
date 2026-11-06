<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MonitoringAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed();
    }

    public function test_guru_is_forbidden_from_monitoring_page(): void
    {
        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();

        $this->actingAs($guru)
            ->get('/admin/monitoring')
            ->assertForbidden();
    }

    public function test_piket_can_open_monitoring_page(): void
    {
        $piket = User::where('email', 'piket@presensi.test')->firstOrFail();

        $this->actingAs($piket)
            ->get('/admin/monitoring')
            ->assertOk();
    }

    public function test_admin_can_open_monitoring_page(): void
    {
        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/monitoring')
            ->assertOk();
    }
}
