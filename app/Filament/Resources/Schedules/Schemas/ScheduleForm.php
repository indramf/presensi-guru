<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teacher_id')
                    ->relationship('teacher', 'full_name')
                    ->label('Guru')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('school_class_id')
                    ->relationship('schoolClass', 'class_name')
                    ->label('Kelas')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('subject_id')
                    ->relationship('subject', 'subject_name')
                    ->label('Mata Pelajaran')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('day_of_week')
                    ->label('Hari')
                    ->options([
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                    ])
                    ->required(),
                TimePicker::make('start_time')
                    ->label('Jam Mulai')
                    ->required(),
                TimePicker::make('end_time')
                    ->label('Jam Selesai')
                    ->required()
                    ->after('start_time'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
