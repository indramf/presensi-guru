<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\SecurityAlert;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SecurityAlertService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Baru → Ditinjau → Ditutup (hanya maju). Peninjau dan waktu tinjau tersimpan. */
    public function changeStatus(
        User $actor,
        SecurityAlert $alert,
        AlertStatus $new,
        ?string $note = null,
    ): SecurityAlert {
        if (Gate::forUser($actor)->denies('update', $alert)) {
            $this->audit->log(
                AuditEventType::AccessDenied,
                AuditResult::Denied,
                actorId: $actor->id,
                targetType: 'SecurityAlert',
                targetId: $alert->id,
                reason: 'alert_review_forbidden',
            );

            throw new AuthorizationException('Anda tidak berwenang meninjau peringatan.');
        }

        return DB::transaction(function () use ($actor, $alert, $new, $note) {
            $locked = SecurityAlert::query()->lockForUpdate()->findOrFail($alert->id);

            if (! in_array($new, $locked->status->allowedTransitions(), true)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status tidak diizinkan.',
                ]);
            }

            $before = ['status' => $locked->status->value];

            $locked->update([
                'status' => $new,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ]);

            $this->audit->log(
                AuditEventType::AlertReviewed,
                actorId: $actor->id,
                targetType: 'SecurityAlert',
                targetId: $locked->id,
                before: $before,
                after: ['status' => $new->value],
                reason: $note !== null ? trim($note) : null,
            );

            return $locked;
        });
    }
}