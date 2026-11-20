<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mata Pelajaran')
                    ->description('Masukkan nama mata pelajaran yang akan digunakan dalam kegiatan akademik.')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        TextInput::make('subject_name')
                            ->label('Nama Mata Pelajaran')
                            ->placeholder('Contoh: Matematika')
                            ->helperText('Nama mata pelajaran harus unik.')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make('Status Mata Pelajaran')
                    ->description('Atur ketersediaan mata pelajaran dalam kegiatan sekolah.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Status Mata Pelajaran')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Mata pelajaran nonaktif tidak digunakan untuk aktivitas baru.'),
                    ]),
            ]);
    }
}
