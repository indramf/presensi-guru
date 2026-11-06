<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Jenis Jadwal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lesson' => 'Pelajaran',
                        'activity' => 'Kegiatan Sekolah',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'lesson' => 'primary',
                        'activity' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('teacher.full_name')
                    ->label('Guru')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schoolClass.class_name')
                    ->label('Kelas')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.subject_name')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                        default => (string) $state,
                    }),
                TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Jadwal')
                    ->options([
                        'lesson' => 'Pelajaran',
                        'activity' => 'Kegiatan Sekolah',
                    ]),
                SelectFilter::make('day_of_week')
                    ->label('Hari')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ]),
                SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'full_name'),
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'class_name'),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->emptyStateHeading('Belum ada jadwal')
            ->emptyStateDescription('Jadwal akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
