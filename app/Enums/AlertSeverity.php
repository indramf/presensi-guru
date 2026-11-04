<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';

    public function label(): string
    {
        return match ($this) {
            self::Rendah => 'Rendah',
            self::Sedang => 'Sedang',
            self::Tinggi => 'Tinggi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Rendah => 'info',
            self::Sedang => 'warning',
            self::Tinggi => 'danger',
        };
    }
}
