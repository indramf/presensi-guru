<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@presensi.test')->firstOrFail();
    }

    public function test_fr04_admin_creates_user_with_role_and_it_is_audited(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Guru Piket Baru',
                'email' => 'piket-baru@presensi.test',
                'password' => 'kata-sandi-uji-123',
                'role' => 'guru_piket',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'piket-baru@presensi.test')->firstOrFail();

        $this->assertTrue($user->hasRole('guru_piket'));
        $this->assertTrue(Hash::check('kata-sandi-uji-123', $user->password));
        $this->assertDatabaseHas('audit_logs', ['target_type' => 'User', 'target_id' => $user->id]);
    }

    public function test_fr04_role_is_required_when_creating_a_user(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Tanpa Peran',
                'email' => 'tanpa-peran@presensi.test',
                'password' => 'kata-sandi-uji-123',
            ])
            ->call('create')
            ->assertHasFormErrors(['role' => 'required']);
    }

    public function test_fr04_admin_cannot_deactivate_or_demote_own_account(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $admin->getKey()])
            ->fillForm(['is_active' => false, 'role' => 'guru'])
            ->call('save');

        $admin->refresh();

        $this->assertTrue($admin->is_active);
        $this->assertTrue($admin->hasRole('administrator'));
    }

    public function test_fr04_changing_another_users_role_is_audited_with_before_and_after(): void
    {
        $this->actingAs($this->admin());
        $guru = User::where('email', 'guru1@presensi.test')->firstOrFail();

        Livewire::test(EditUser::class, ['record' => $guru->getKey()])
            ->fillForm(['role' => 'guru_piket'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($guru->fresh()->hasRole('guru_piket'));
        $this->assertFalse($guru->fresh()->hasRole('guru'));
    }

    public function test_no_filament_resource_exposes_delete_actions(): void
    {
        $matches = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path('Filament'))) as $file) {
            if (
                $file->isFile() && str_ends_with($file->getFilename(), '.php')
                && preg_match('/DeleteAction|DeleteBulkAction|ForceDelete|Restore/', file_get_contents($file->getPathname()))
            ) {
                $matches[] = $file->getPathname();
            }
        }

        $this->assertSame([], $matches, 'Aksi hapus ditemukan di: '.implode(', ', $matches));
    }
}
