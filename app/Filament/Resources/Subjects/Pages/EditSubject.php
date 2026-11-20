<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Resources\Concerns\ConfirmsDeactivation;
use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Resources\Pages\EditRecord;

class EditSubject extends EditRecord
{
    use ConfirmsDeactivation;

    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return SubjectResource::getUrl('index');
    }
}
