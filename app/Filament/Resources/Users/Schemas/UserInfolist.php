<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('username')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email'),
                TextEntry::make('email_verified_at')
                    ->label('Email Terverifikasi Pada')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('-'),
                TextEntry::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('-'),
            ]);
    }
}
