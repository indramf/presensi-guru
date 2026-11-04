<?php

namespace App\Enums;

enum AnomalyRule: string
{
    case An01 = 'AN-01';
    case An02 = 'AN-02';
    case An03 = 'AN-03';
    case An04 = 'AN-04';
    case An05 = 'AN-05';
    case An06 = 'AN-06';
    case An07 = 'AN-07';

    public function description(): string
    {
        return match ($this) {
            self::An01 => 'Percobaan login gagal berulang pada akun yang sama.',
            self::An02 => 'Presensi dilakukan di luar jendela waktu yang diizinkan.',
            self::An03 => 'Terdapat percobaan presensi ganda.',
            self::An04 => 'Terjadi akses yang ditolak secara berulang.',
            self::An05 => 'Terjadi perubahan status presensi yang telah diverifikasi.',
            self::An06 => 'Terdapat perubahan jadwal mendadak atau aktivitas pada hari yang tidak sesuai.',
            self::An07 => 'Terdapat aktivitas administrator yang tidak biasa.',
        };
    }
}
