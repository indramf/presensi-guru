<?php

namespace App\Enums;

enum AlertStatus: string
{
    case Baru = 'baru';
    case Ditinjau = 'ditinjau';
    case Ditutup = 'ditutup';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Ditinjau => 'Ditinjau',
            self::Ditutup => 'Ditutup',
        };
    }

    /** @return list<self> status tujuan yang diizinkan dari status ini */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Baru => [self::Ditinjau, self::Ditutup],
            self::Ditinjau => [self::Ditutup],
            self::Ditutup => [],
        };
    }
}
