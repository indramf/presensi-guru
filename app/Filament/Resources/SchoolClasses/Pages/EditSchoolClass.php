<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Filament\Resources\Concerns\ConfirmsDeactivation;
use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use Filament\Resources\Pages\EditRecord;

class EditSchoolClass extends EditRecord
{
    use ConfirmsDeactivation;

    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return SchoolClassResource::getUrl('index');
    }
}
