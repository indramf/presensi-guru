<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeacherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Akun Pengguna')
                    ->placeholder('-'),
                TextEntry::make('teacher_code')
                    ->label('Kode Guru'),
                TextEntry::make('full_name')
                    ->label('Nama Lengkap'),
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
