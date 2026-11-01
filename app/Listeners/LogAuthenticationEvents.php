<?php

namespace App\Listeners;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvents
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handleLogin(Login $event): void
    {
        $id = (int) $event->user->getAuthIdentifier();

        $this->audit->log(
            AuditEventType::LoginSuccess,
            actorId: $id,
            targetType: 'user',
            targetId: $id,
        );
    }

    public function handleFailed(Failed $event): void
    {
        // HANYA identitas login yang dicatat. Password tidak pernah disentuh.
        $login = $event->credentials['email'] ?? $event->credentials['username'] ?? null;

        $this->audit->log(
            AuditEventType::LoginFailed,
            AuditResult::Failed,
            actorId: $event->user ? (int) $event->user->getAuthIdentifier() : null,
            after: ['login' => $login],
        );
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user === null) {
            return;
        }

        $id = (int) $event->user->getAuthIdentifier();

        $this->audit->log(
            AuditEventType::Logout,
            actorId: $id,
            targetType: 'user',
            targetId: $id,
        );
    }
}
