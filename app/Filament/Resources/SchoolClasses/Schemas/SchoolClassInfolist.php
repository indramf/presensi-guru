<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolClassInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kelas')
                    ->description('Identitas dan tahun ajaran kelas.')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('class_name')
                            ->label('Nama Kelas')
                            ->icon('heroicon-o-building-library')
                            ->weight('medium'),

                        TextEntry::make('academic_year')
                            ->label('Tahun Ajaran')
                            ->icon('heroicon-o-calendar'),
                    ]),

                Section::make('Status Kelas')
                    ->description('Informasi status penggunaan kelas.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Status Kelas')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->icon(fn (bool $state): string => $state
                                ? 'heroicon-o-check-circle'
                                : 'heroicon-o-x-circle')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Riwayat pembuatan dan perubahan data kelas.')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i', 'Asia/Jakarta')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d M Y H:i', 'Asia/Jakarta')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
