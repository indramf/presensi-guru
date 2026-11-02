<?php

namespace App\Observers;

use App\Enums\AuditEventType;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function created(Model $model): void
    {
        $this->write(AuditEventType::DataCreated, $model, after: $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $before = array_intersect_key($model->getOriginal(), $changes);

        $event = array_key_exists('is_active', $changes) && ! $model->is_active
            ? AuditEventType::DataDeactivated
            : AuditEventType::DataUpdated;

        $this->write($event, $model, $before, $changes);
    }

    private function write(AuditEventType $event, Model $model, ?array $before = null, ?array $after = null): void
    {
        // Hanya perubahan oleh pengguna yang login (lewat aplikasi).
        // Seeder, tinker, dan test tanpa login tidak mengisi audit log.
        if (! Auth::check()) {
            return;
        }

        $this->audit->log(
            $event,
            actorId: (int) Auth::id(),
            targetType: class_basename($model),
            targetId: (int) $model->getKey(),
            before: $before,
            after: $after,
        );
    }
}
