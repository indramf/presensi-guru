<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case Belum = 'belum';
    case Proses = 'proses';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Belum => 'Belum',
            self::Proses => 'Dalam Proses',
            self::Selesai => 'Selesai',
        };
    }
}
