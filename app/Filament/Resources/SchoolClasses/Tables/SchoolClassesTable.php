<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchoolClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('class_name')
                    ->label('Nama Kelas')
                    ->searchable(),
                TextColumn::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->emptyStateHeading('Belum ada kelas')
            ->emptyStateDescription('Kelas akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-building-office');
    }
}
