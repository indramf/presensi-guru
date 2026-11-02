<?php

namespace App\Enums;

enum SessionState: string
{
    case NotOpen = 'not_open';
    case Open = 'open';
    case Recorded = 'recorded';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NotOpen => 'Belum dibuka',
            self::Open => 'Dapat diisi',
            self::Recorded => 'Sudah diisi',
            self::Closed => 'Belum diisi',
        };
    }
}
