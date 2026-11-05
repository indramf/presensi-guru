<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Concerns\ConfirmsDeactivation;
use App\Filament\Resources\Teachers\TeacherResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTeacher extends EditRecord
{
    use ConfirmsDeactivation;

    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
