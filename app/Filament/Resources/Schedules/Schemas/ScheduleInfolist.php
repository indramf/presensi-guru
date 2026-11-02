<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('teacher.id')
                    ->label('Teacher'),
                TextEntry::make('class_id')
                    ->numeric(),
                TextEntry::make('subject.id')
                    ->label('Subject'),
                TextEntry::make('day_of_week')
                    ->numeric(),
                TextEntry::make('start_time')
                    ->time(),
                TextEntry::make('end_time')
                    ->time(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
