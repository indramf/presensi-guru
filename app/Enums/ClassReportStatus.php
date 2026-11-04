<?php

namespace App\Enums;

enum ClassReportStatus: string
{
    case GuruHadir = 'guru_hadir';
    case KelasKosong = 'kelas_kosong';
    case KelasDiberiTugas = 'kelas_diberi_tugas';
    case GuruPengganti = 'guru_pengganti';

    public function label(): string
    {
        return match ($this) {
            self::GuruHadir => 'Guru Hadir',
            self::KelasKosong => 'Kelas Kosong',
            self::KelasDiberiTugas => 'Kelas Diberi Tugas',
            self::GuruPengganti => 'Guru Pengganti',
        };
    }
}
