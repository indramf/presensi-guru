<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacher extends ViewRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                // ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(TeacherResource::getUrl('index')),
            EditAction::make()
                ->label('Ubah'),
        ];
    }
}
