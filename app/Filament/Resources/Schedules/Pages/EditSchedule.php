<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Concerns\ConfirmsDeactivation;
use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSchedule extends EditRecord
{
    use ConfirmsDeactivation;

    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
