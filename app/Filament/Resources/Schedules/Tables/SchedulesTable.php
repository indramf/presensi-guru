<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                //
            ]);
    }
}
