<?php

namespace App\Services;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class AuditVerificationService
{
    public function __construct(
        private readonly AuditIntegrityChecker $checker,
        private readonly AuditLogger $audit,
    ) {}

    /** @return array{ok: bool, checked: int, problems: list<string>} */
    public function run(User $actor): array
    {
        if (Gate::forUser($actor)->denies('verify', AuditLog::class)) {
            throw new AuthorizationException(
                'Anda tidak berwenang memeriksa integritas audit log.'
            );
        }

        $result = $this->checker->verify();

        // Catat setelah verifikasi agar tidak memengaruhi hasil pemeriksaan sendiri.
        $this->audit->log(
            AuditEventType::IntegrityChecked,
            $result['ok'] ? AuditResult::Success : AuditResult::Failed,
            actorId: $actor->id,
            after: [
                'checked' => $result['checked'],
                'problems' => count($result['problems']),
            ],
        );

        return $result;
    }
}
