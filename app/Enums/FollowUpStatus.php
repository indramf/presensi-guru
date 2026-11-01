<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case Belum = 'belum';
    case Proses = 'proses';
    case Selesai = 'selesai';
}
