<?php

namespace App\Events;

use App\Models\AuditLog;

class AuditEntryRecorded
{
    public function __construct(public readonly AuditLog $entry) {}
}
