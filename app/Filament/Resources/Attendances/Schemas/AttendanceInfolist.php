<?php

namespace App\Filament\Resources\Attendances\Schemas;

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
                    ->badge(),

                TextEntry::make('recorder.name')
                    ->label('Dicatat Oleh')
                    ->placeholder('-'),

                IconEntry::make('verified_at')
                    ->label('Verifikasi')
                    ->boolean(),

                TextEntry::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Waktu Pencatatan')
                    ->dateTime('d M Y H:i'),

                TextEntry::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i'),
            ]);
    }
}
