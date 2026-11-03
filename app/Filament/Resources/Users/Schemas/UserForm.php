<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Nama Pengguna')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Select::make('role')
                    ->label('Peran')
                    ->options(
                        collect(UserRole::cases())
                            ->mapWithKeys(fn (UserRole $role) => [
                                $role->value => $role->label(),
                            ])
                            ->all()
                    )
                    ->required()
                    ->dehydrated(false), // bukan kolom di tabel users; disimpan lewat hook halaman
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->disabled(fn (?Model $record): bool => $record?->is(Auth::user()) ?? false)
                    ->helperText('Akun nonaktif tidak dapat masuk. Akun Anda sendiri tidak dapat dinonaktifkan.'),
            ]);
    }
}
