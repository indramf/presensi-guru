<?php

namespace App\Services\Anomaly;

use App\Enums\AlertSeverity;
use App\Enums\AnomalyRule;

final class AlertData
{
    public function __construct(
        public readonly AnomalyRule $rule,
        public readonly AlertSeverity $severity,
        public readonly string $description,
        public readonly ?int $relatedUserId,
        public readonly int $relatedAuditLogId,
        public readonly int $dedupeMinutes = 60,
    ) {}
}
