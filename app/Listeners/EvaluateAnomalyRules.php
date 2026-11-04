<?php

namespace App\Listeners;

use App\Events\AuditEntryRecorded;
use App\Services\Anomaly\AnomalyEngine;
use Throwable;

class EvaluateAnomalyRules
{
    public function __construct(private readonly AnomalyEngine $engine) {}

    public function handle(AuditEntryRecorded $event): void
    {
        try {
            $this->engine->evaluate($event->entry);
        } catch (Throwable $e) {
            report($e);   // kegagalan deteksi tidak boleh mengganggu alur utama
        }
    }
}