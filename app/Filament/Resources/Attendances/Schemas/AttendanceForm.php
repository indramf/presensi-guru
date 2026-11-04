<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Enums\AttendanceStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class AttendanceForm
{
    public static function statusChange(): array
    {
        return [
            Select::make('status')
                ->label('Status Baru')
                ->options(AttendanceStatus::class)
                ->required(),

            Textarea::make('reason')
                ->label('Alasan')
                ->placeholder('Minimal 10 karakter...')
                ->required()
                ->minLength(10),
        ];
    }
}
