<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('teacher.full_name')
                    ->label('Guru'),
                TextEntry::make('schoolClass.class_name')
                    ->label('Kelas'),
                TextEntry::make('subject.subject_name')
                    ->label('Mata Pelajaran'),
                TextEntry::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        default => $state,
                    }),
                TextEntry::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),
                TextEntry::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),
                TextEntry::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('-'),
            ]);
    }
}
