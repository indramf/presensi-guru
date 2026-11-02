<?php

namespace App\Enums;

enum MonitoringStatus: string
{
    case Menunggu = 'menunggu';
    case Hadir = 'hadir';
    case Terlambat = 'terlambat';
    case Izin = 'izin';
    case TidakHadir = 'tidak_hadir';
    case BelumTerkonfirmasi = 'belum_terkonfirmasi';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Hadir => 'Hadir',
            self::Terlambat => 'Terlambat',
            self::Izin => 'Izin',
            self::TidakHadir => 'Tidak hadir',
            self::BelumTerkonfirmasi => 'Belum terkonfirmasi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Menunggu, self::BelumTerkonfirmasi => 'gray',
            self::Hadir => 'success',
            self::Terlambat => 'warning',
            self::Izin => 'info',
            self::TidakHadir => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Menunggu => 'heroicon-o-ellipsis-horizontal-circle',
            self::Hadir => 'heroicon-o-check-circle',
            self::Terlambat => 'heroicon-o-clock',
            self::Izin => 'heroicon-o-document-text',
            self::TidakHadir => 'heroicon-o-x-circle',
            self::BelumTerkonfirmasi => 'heroicon-o-question-mark-circle',
        };
    }
}
