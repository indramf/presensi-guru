<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Tables\Columns\IconColumn;
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
                TextColumn::make('teacher.full_name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schoolClass.class_name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.subject_name')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                TextColumn::make('day_of_week')
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
                TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Hari')
                    ->options([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu']),
                SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'full_name'),
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'class_name'),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ]);
    }
}
