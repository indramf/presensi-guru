<?php

namespace App\Enums;

enum AuditResult: string
{
    case Success = 'success';
    case Denied = 'denied';
    case Failed = 'failed';
}
