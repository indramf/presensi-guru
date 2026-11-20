<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class SchoolClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kelas')
                    ->description('Masukkan identitas kelas dan tahun ajaran yang berlaku.')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        TextInput::make('class_name')
                            ->label('Nama Kelas')
                            ->placeholder('Contoh: IV TKJT')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where(
                                    'academic_year',
                                    $get('academic_year')
                                )
                            ),

                        TextInput::make('academic_year')
                            ->label('Tahun Ajaran')
                            ->placeholder('Contoh: 2026/2027')
                            ->helperText('Gunakan format tahun awal/tahun akhir.')
                            ->required()
                            ->regex('/^\d{4}\/\d{4}$/'),
                    ]),

                Section::make('Status Kelas')
                    ->description('Atur apakah kelas masih digunakan dalam kegiatan sekolah.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Status Kelas')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Kelas nonaktif tidak digunakan untuk aktivitas baru.'),
                    ]),
            ]);
    }
}
