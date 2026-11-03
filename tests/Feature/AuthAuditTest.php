<?php

namespace Tests\Feature;

use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_fr02_failed_login_is_logged_without_password(): void
    {
        event(new Failed('web', null, [
            'email' => 'tidak-ada@presensi.test',
            'password' => 'rahasia-banget',
        ]));

        $log = AuditLog::firstOrFail();

        $this->assertSame(AuditEventType::LoginFailed, $log->event_type);
        $this->assertNull($log->actor_user_id);
        $this->assertSame('tidak-ada@presensi.test', $log->after_data['login']);
        $this->assertStringNotContainsString('rahasia-banget', json_encode($log->after_data));
    }

    public function test_fr12_successful_login_and_logout_are_logged(): void
    {
        $user = User::factory()->create();

        event(new Login('web', $user, false));
        event(new Logout('web', $user));

        $types = AuditLog::orderBy('id')->get()->pluck('event_type')->all();

        $this->assertSame([AuditEventType::LoginSuccess, AuditEventType::Logout], $types);
    }
}
