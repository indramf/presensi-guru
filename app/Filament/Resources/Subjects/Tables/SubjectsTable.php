<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject_name')
                    ->label('Nama Mata Pelajaran')
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
            ->emptyStateHeading('Belum ada mata pelajaran')
            ->emptyStateDescription('Mata pelajaran akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-book-open');
    }
}
