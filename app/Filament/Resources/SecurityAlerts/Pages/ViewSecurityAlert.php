<?php

namespace App\Filament\Resources\SecurityAlerts\Pages;

use App\Filament\Resources\SecurityAlerts\SecurityAlertResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSecurityAlert extends ViewRecord
{
    protected static string $resource = SecurityAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
