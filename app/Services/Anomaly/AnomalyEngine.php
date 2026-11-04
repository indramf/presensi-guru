<?php

namespace App\Services\Anomaly;

use App\Enums\AlertStatus;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use Illuminate\Support\Collection;
use Throwable;

class AnomalyEngine
{
    /** @return Collection<int, SecurityAlert> peringatan baru yang dibuat */
    public function evaluate(AuditLog $entry): Collection
    {
        $created = collect();

        foreach (config('anomaly.rules', []) as $ruleClass) {
            try {
                $alert = app($ruleClass)->evaluate($entry);

                if ($alert === null || $this->isDuplicate($alert)) {
                    continue;
                }

                $created->push(SecurityAlert::create([
                    'rule_code' => $alert->rule,
                    'severity' => $alert->severity,
                    'related_user_id' => $alert->relatedUserId,
                    'related_audit_log_id' => $alert->relatedAuditLogId,
                    'description' => $alert->description,
                    'detected_at' => now(),
                    'status' => AlertStatus::Baru,
                ]));
            } catch (Throwable $e) {
                report($e);   // satu aturan gagal tidak menghentikan aturan lain
            }
        }

        return $created;
    }

    /** Peringatan identik yang masih terbuka dalam jendela waktu digabung. */
    private function isDuplicate(AlertData $alert): bool
    {
        return SecurityAlert::query()
            ->where('rule_code', $alert->rule->value)
            ->where('description', $alert->description)
            ->where('related_user_id', $alert->relatedUserId)
            ->whereIn('status', [AlertStatus::Baru->value, AlertStatus::Ditinjau->value])
            ->where('detected_at', '>=', now()->subMinutes($alert->dedupeMinutes))
            ->exists();
    }
}