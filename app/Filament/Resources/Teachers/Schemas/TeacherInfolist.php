<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Models\Teacher;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeacherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Guru')
                    ->description('Identitas dan informasi utama guru.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('teacher_code')
                            ->label('Kode Guru')
                            ->icon('heroicon-o-identification')
                            ->copyable(),
                        TextEntry::make('full_name')
                            ->label('Nama Lengkap')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('subjects')
                            ->label('Mata Pelajaran')
                            ->state(fn(Teacher $record): string => $record->schedules
                                ->pluck('subject.subject_name')
                                ->filter()
                                ->unique()
                                ->values()
                                ->implode(', '))
                            ->placeholder('Belum ada mata pelajaran')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Akun')
                    ->description('Informasi akun yang terhubung dengan guru.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Akun Pengguna')
                            ->placeholder('Belum terhubung')
                            ->icon('heroicon-o-identification'),

                        TextEntry::make('is_active')
                            ->label('Status Guru')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->icon(fn(bool $state): string => $state
                                ? 'heroicon-o-check-circle'
                                : 'heroicon-o-x-circle')
                            ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Riwayat pembuatan dan perubahan data guru.')
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
