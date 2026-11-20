<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mata Pelajaran')
                    ->description('Identitas mata pelajaran yang terdaftar dalam sistem.')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        TextEntry::make('subject_name')
                            ->label('Nama Mata Pelajaran')
                            ->icon('heroicon-o-academic-cap')
                            ->weight('medium'),
                    ]),

                Section::make('Status Mata Pelajaran')
                    ->description('Informasi ketersediaan mata pelajaran dalam kegiatan sekolah.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Status Mata Pelajaran')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->icon(fn (bool $state): string => $state
                                ? 'heroicon-o-check-circle'
                                : 'heroicon-o-x-circle')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Riwayat pembuatan dan perubahan data mata pelajaran.')
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
