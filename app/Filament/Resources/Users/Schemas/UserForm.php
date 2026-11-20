<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Informasi dasar akun pengguna.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label('Nama Pengguna')
                            ->placeholder('Masukkan nama pengguna')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->placeholder('contoh@email.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Hak Akses')
                    ->description('Atur peran dan status akun pengguna.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->label('Peran Pengguna')
                            ->options(
                                collect(UserRole::cases())
                                    ->mapWithKeys(fn (UserRole $role) => [
                                        $role->value => $role->label(),
                                    ])
                                    ->all()
                            )
                            ->required()
                            ->dehydrated(false),

                        Toggle::make('is_active')
                            ->label('Status Akun')
                            ->default(true)
                            ->inline(false)
                            ->disabled(
                                fn (?Model $record): bool =>
                                    $record?->is(Auth::user()) ?? false
                            )
                            ->helperText(
                                'Akun nonaktif tidak dapat masuk ke aplikasi.'
                            ),
                    ]),

                Section::make('Keamanan Akun')
                    ->description('Buat password untuk akun baru.')
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->revealable()
                            ->placeholder('Masukkan kata sandi')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->required(
                                fn (string $operation): bool =>
                                    $operation === 'create'
                            )
                            ->maxLength(255),
                    ])
                    ->visible(
                        fn (string $operation): bool =>
                            $operation === 'create'
                    ),
            ]);
    }
}
