<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Guru = 'guru';
    case GuruPiket = 'guru_piket';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Guru => 'Guru',
            self::GuruPiket => 'Guru Piket',
        };
    }
}
