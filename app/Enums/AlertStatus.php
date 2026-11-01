<?php

namespace App\Enums;

enum AlertStatus: string
{
    case Baru = 'baru';
    case Ditinjau = 'ditinjau';
    case Ditutup = 'ditutup';
}
