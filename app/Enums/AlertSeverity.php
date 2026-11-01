<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';
}
