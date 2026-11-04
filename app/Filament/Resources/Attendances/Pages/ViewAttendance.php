<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Schemas\AttendanceInfolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAttendance extends ViewRecord
{
    protected static string $resource = AttendanceResource::class;

    public function infolist(Schema $schema): Schema
    {
        return AttendanceInfolist::configure($schema);
    }
}
