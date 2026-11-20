<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Guru')
                    ->description('Identitas dan data utama guru.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('teacher_code')
                            ->label('Kode Guru')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                    ]),

                Section::make('Pengaturan Akun')
                    ->description('Hubungkan data guru dengan akun pengguna.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Akun Pengguna')
                            ->placeholder('Pilih akun pengguna')
                            ->searchable()
                            ->preload()
                            ->helperText('Opsional. Hubungkan guru dengan akun untuk akses aplikasi.'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Guru nonaktif tidak dapat digunakan dalam operasional presensi.'),
                    ]),
            ]);
    }
}
