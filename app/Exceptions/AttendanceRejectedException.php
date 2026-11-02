<?php

namespace App\Exceptions;

use App\Enums\AttendanceRejection;
use RuntimeException;

class AttendanceRejectedException extends RuntimeException
{
    public function __construct(public readonly AttendanceRejection $reason)
    {
        parent::__construct($reason->message());
    }
}
