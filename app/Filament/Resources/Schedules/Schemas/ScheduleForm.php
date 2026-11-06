<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Jenis Jadwal')
                    ->options([
                        'lesson' => 'Pelajaran',
                        'activity' => 'Kegiatan Sekolah',
                    ])
                    ->default('lesson')
                    ->required()
                    ->live(),
                Select::make('teacher_id')
                    ->relationship('teacher', 'full_name')
                    ->label('Guru')
                    ->required(fn (Get $get): bool => $get('type') === 'lesson')
                    ->visible(fn (Get $get): bool => $get('type') === 'lesson')
                    ->searchable()
                    ->preload(),
                Select::make('class_id')
                    ->relationship('schoolClass', 'class_name')
                    ->label('Kelas')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('subject_id')
                    ->relationship('subject', 'subject_name')
                    ->label('Mata Pelajaran')
                    ->required(fn (Get $get): bool => $get('type') === 'lesson')
                    ->visible(fn (Get $get): bool => $get('type') === 'lesson')
                    ->searchable()
                    ->preload(),
                TextInput::make('activity_name')
                    ->label('Nama Kegiatan')
                    ->required(fn (Get $get): bool => $get('type') === 'activity')
                    ->visible(fn (Get $get): bool => $get('type') === 'activity')
                    ->maxLength(255),
                Select::make('day_of_week')
                    ->label('Hari')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ])
                    ->required()
                    ->native(false),
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
