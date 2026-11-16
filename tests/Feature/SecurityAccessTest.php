<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_fr15_only_admin_can_open_security_pages(): void
    {
        $urls = ['/admin/security-alerts', '/admin/audit-logs'];

        foreach (['guru1@presensi.test', 'piket@presensi.test'] as $email) {
            $this->flushSession();

            $user = User::where('email', $email)->firstOrFail();

            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }

        $this->flushSession();

        $admin = User::where('email', 'admin@presensi.test')->firstOrFail();

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
