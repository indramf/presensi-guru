<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jadwal')
                    ->description('Informasi utama jadwal pembelajaran atau kegiatan sekolah.')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')
                            ->label('Jenis Jadwal')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'lesson' => 'Pelajaran',
                                'activity' => 'Kegiatan Sekolah',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'lesson' => 'info',
                                'activity' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('schoolClass.class_name')
                            ->label('Kelas')
                            ->icon('heroicon-o-academic-cap'),

                        TextEntry::make('teacher.full_name')
                            ->label('Guru')
                            ->icon('heroicon-o-user')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->type === 'lesson'),

                        TextEntry::make('subject.subject_name')
                            ->label('Mata Pelajaran')
                            ->icon('heroicon-o-book-open')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->type === 'lesson'),

                        TextEntry::make('activity_name')
                            ->label('Nama Kegiatan')
                            ->icon('heroicon-o-clipboard-document')
                            ->placeholder('—')
                            ->visible(fn ($record): bool => $record?->type === 'activity')
                            ->columnSpanFull(),
                    ]),

                Section::make('Waktu Pelaksanaan')
                    ->description('Hari dan rentang waktu pelaksanaan jadwal.')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('day_of_week')
                            ->label('Hari')
                            ->icon('heroicon-o-calendar')
                            ->formatStateUsing(fn ($state): string => match ((int) $state) {
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                7 => 'Minggu',
                                default => (string) $state,
                            }),

                        TextEntry::make('start_time')
                            ->label('Jam Mulai')
                            ->time('H:i')
                            ->icon('heroicon-o-play'),

                        TextEntry::make('end_time')
                            ->label('Jam Selesai')
                            ->time('H:i')
                            ->icon('heroicon-o-stop'),
                    ]),

                Section::make('Status Jadwal')
                    ->description('Informasi ketersediaan jadwal.')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Status Jadwal')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->icon(fn (bool $state): string => $state
                                ? 'heroicon-o-check-circle'
                                : 'heroicon-o-x-circle')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Riwayat pembuatan dan perubahan data jadwal.')
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
