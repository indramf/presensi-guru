<?php

namespace App\Filament\Resources\Teachers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher_code')
                    ->label('Kode Guru')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Akun Pengguna'),
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
            ->emptyStateHeading('Belum ada guru')
            ->emptyStateDescription('Data guru akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
