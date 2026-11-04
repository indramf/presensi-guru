<?php

namespace App\Services\Anomaly;

use App\Models\AuditLog;

interface Detector
{
    /** Mengembalikan data peringatan, atau null bila entri ini tidak memicu aturan. */
    public function evaluate(AuditLog $entry): ?AlertData;
}