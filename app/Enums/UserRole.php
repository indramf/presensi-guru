<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Guru = 'guru';
    case GuruPiket = 'guru_piket';
}
