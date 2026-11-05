<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Enums\AttendanceStatus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('attendance_date')
                    ->label('Tanggal')
                    ->date('d M Y'),
                TextEntry::make('schedule.start_time')
                    ->label('Jam')
                    ->time('H:i'),
                TextEntry::make('teacher.user.name')
                    ->label('Guru'),
                TextEntry::make('schedule.schoolClass.class_name')
                    ->label('Kelas'),
                TextEntry::make('schedule.subject.name')
                    ->label('Mata Pelajaran'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AttendanceStatus $state): string => match ($state) {
                        AttendanceStatus::Hadir => 'Hadir',
                        AttendanceStatus::Terlambat => 'Terlambat',
                        AttendanceStatus::Izin => 'Izin',
                        AttendanceStatus::TidakHadir => 'Tidak hadir',
                    })
                    ->icon(fn (AttendanceStatus $state): string => match ($state) {
                        AttendanceStatus::Hadir => 'heroicon-o-check-circle',
                        AttendanceStatus::Terlambat => 'heroicon-o-clock',
                        AttendanceStatus::Izin => 'heroicon-o-document-text',
                        AttendanceStatus::TidakHadir => 'heroicon-o-x-circle',
                    })
                    ->color(fn (AttendanceStatus $state): string => match ($state) {
                        AttendanceStatus::Hadir => 'success',
                        AttendanceStatus::Terlambat => 'warning',
                        AttendanceStatus::Izin => 'info',
                        AttendanceStatus::TidakHadir => 'danger',
                    }),
                TextEntry::make('recorder.name')
                    ->label('Dicatat Oleh')
                    ->placeholder('Tidak diketahui'),
                TextEntry::make('recorded_at')
                    ->label('Waktu Dicatat')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta'),
                IconEntry::make('verified_at')
                    ->label('Verifikasi')
                    ->boolean(),
                TextEntry::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('Belum terverifikasi'),
                TextEntry::make('verified_at')
                    ->label('Waktu Verifikasi')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('Belum terverifikasi'),
                TextEntry::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i'),
            ]);
    }
}
