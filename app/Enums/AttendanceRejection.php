<?php

namespace App\Enums;

enum AttendanceRejection: string
{
    case NotYourSchedule = 'not_your_schedule';
    case InactiveSchedule = 'inactive_schedule';
    case WrongDay = 'wrong_day';
    case OutsideWindow = 'outside_window';
    case Duplicate = 'duplicate';

    public function message(): string
    {
        return match ($this) {
            self::NotYourSchedule => 'Jadwal ini bukan milik Anda.',
            self::InactiveSchedule => 'Jadwal ini sudah tidak aktif.',
            self::WrongDay => 'Jadwal ini bukan untuk hari ini.',
            self::OutsideWindow => 'Presensi hanya dapat diisi mulai '
                .config('attendance.open_before_minutes').' menit sebelum jam pelajaran sampai jam pelajaran berakhir.',
            self::Duplicate => 'Presensi untuk sesi ini sudah tercatat.',
        };
    }
}
