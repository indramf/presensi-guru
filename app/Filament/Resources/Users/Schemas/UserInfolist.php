<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Informasi identitas dan akun pengguna.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Lengkap')
                            ->icon('heroicon-o-user')
                            ->weight('medium'),

                        TextEntry::make('username')
                            ->label('Username')
                            ->icon('heroicon-o-at-symbol')
                            ->placeholder('-'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->columnSpanFull(),
                    ]),

                Section::make('Hak Akses dan Keamanan')
                    ->description('Informasi peran dan keamanan akun pengguna.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('roles.name')
                            ->label('Peran')
                            ->state(fn ($record): string => $record->getRoleNames()->implode(', '))
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('is_active')
                            ->label('Status Akun')
                            ->badge()
                            ->formatStateUsing(
                                fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif'
                            )
                            ->icon(
                                fn (bool $state): string => $state
                                    ? 'heroicon-o-check-circle'
                                    : 'heroicon-o-x-circle'
                            )
                            ->color(
                                fn (bool $state): string => $state ? 'success' : 'gray'
                            ),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Informasi waktu pencatatan data pengguna.')
                    ->icon('heroicon-o-clock')
                    ->collapsed()
                    ->columns(2)
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
