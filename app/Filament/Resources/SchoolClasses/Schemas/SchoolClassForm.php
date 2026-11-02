<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Get;

class SchoolClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('class_name')
                    ->label('Nama Kelas')
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('academic_year', $get('academic_year'))
                    ),
                TextInput::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->required()
                    ->regex('/^\d{4}\/\d{4}$/')
                    ->placeholder('Contoh: 2026/2027'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}