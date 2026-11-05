<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_fr02_repeated_failed_logins_block_even_the_correct_password(): void
    {
        $user = User::where('email', 'guru1@presensi.test')->firstOrFail();

        for ($i = 0; $i < 10; $i++) {
            Livewire::test(Login::class)
                ->fillForm(['email' => $user->email, 'password' => 'salah'])
                ->call('authenticate');
        }

        Livewire::test(Login::class)
            ->fillForm(['email' => $user->email, 'password' => 'password'])
            ->call('authenticate');

        $this->assertGuest();
    }
}
