<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jadwal')
                    ->description('Tentukan jenis jadwal dan informasi pelaksanaan pembelajaran atau kegiatan sekolah.')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Jadwal')
                            ->options([
                                'lesson' => 'Pelajaran',
                                'activity' => 'Kegiatan Sekolah',
                            ])
                            ->default('lesson')
                            ->required()
                            ->live()
                            ->native(false)
                            ->columnSpanFull(),

                        Select::make('teacher_id')
                            ->relationship('teacher', 'full_name')
                            ->label('Guru')
                            ->placeholder('Pilih guru')
                            ->required(fn (Get $get): bool => $get('type') === 'lesson')
                            ->visible(fn (Get $get): bool => $get('type') === 'lesson')
                            ->searchable()
                            ->preload(),

                        Select::make('subject_id')
                            ->relationship('subject', 'subject_name')
                            ->label('Mata Pelajaran')
                            ->placeholder('Pilih mata pelajaran')
                            ->required(fn (Get $get): bool => $get('type') === 'lesson')
                            ->visible(fn (Get $get): bool => $get('type') === 'lesson')
                            ->searchable()
                            ->preload(),

                        TextInput::make('activity_name')
                            ->label('Nama Kegiatan')
                            ->placeholder('Contoh: Upacara Bendera')
                            ->required(fn (Get $get): bool => $get('type') === 'activity')
                            ->visible(fn (Get $get): bool => $get('type') === 'activity')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('class_id')
                            ->relationship('schoolClass', 'class_name')
                            ->label('Kelas')
                            ->placeholder('Pilih kelas')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

                Section::make('Waktu Pelaksanaan')
                    ->description('Atur hari dan rentang waktu pelaksanaan jadwal.')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
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
                            ->placeholder('Pilih hari')
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TimePicker::make('start_time')
                                    ->label('Jam Mulai')
                                    ->displayFormat('H:i')
                                    ->seconds(false)
                                    ->required(),

                                TimePicker::make('end_time')
                                    ->label('Jam Selesai')
                                    ->displayFormat('H:i')
                                    ->seconds(false)
                                    ->required()
                                    ->after('start_time')
                                    ->validationMessages([
                                        'after' => 'Jam selesai harus setelah jam mulai.',
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Status Jadwal')
                    ->description('Atur ketersediaan jadwal dalam kegiatan sekolah.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Status Jadwal')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Jadwal nonaktif tidak digunakan dalam aktivitas baru.'),
                    ]),
            ]);
    }
}
