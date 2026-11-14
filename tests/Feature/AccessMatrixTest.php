<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    private function master(): array
    {
        return ['/admin/users', '/admin/teachers', '/admin/school-classes', '/admin/subjects', '/admin/schedules'];
    }

    private function security(): array
    {
        return ['/admin/security-alerts', '/admin/audit-logs'];
    }

    /** Peran => [status HTTP => daftar halaman]. */
    private function matrix(): array
    {
        return [
            'admin@presensi.test' => [
                200 => [...$this->master(), ...$this->security(), '/admin/monitoring', '/admin/attendances'],
                403 => ['/admin/my-attendance'],
            ],
            'piket@presensi.test' => [
                200 => ['/admin/monitoring', '/admin/attendances'],
                403 => [...$this->master(), ...$this->security(), '/admin/my-attendance'],
            ],
            'guru1@presensi.test' => [
                200 => ['/admin/my-attendance'],
                403 => [...$this->master(), ...$this->security(), '/admin/monitoring', '/admin/attendances'],
            ],
        ];
    }

    public function test_t07_every_role_sees_exactly_the_pages_it_is_allowed_to(): void
    {
        $failures = [];

        foreach ($this->matrix() as $email => $byStatus) {
            $user = User::where('email', $email)->firstOrFail();

            foreach ($byStatus as $expected => $urls) {
                foreach ($urls as $url) {
                    $this->flushSession();
                    $this->actingAs($user);

                    $response = $this->get($url);
                    $actual = $response->getStatusCode();

                    if ($actual !== $expected) {
                        $failures[] = "{$email} {$url}: diharapkan {$expected}, aktual {$actual}";
                    }
                }
            }
        }

        $this->assertSame([], $failures, "Selisih akses:\n".implode("\n", $failures));
    }

    public function test_t07_guests_are_redirected_to_login_on_every_page(): void
    {
        $all = [...$this->master(), ...$this->security(), '/admin/monitoring', '/admin/attendances', '/admin/my-attendance'];

        foreach ($all as $url) {
            $this->get($url)->assertRedirect();
        }
    }
}
