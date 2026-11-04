<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages;
use App\Filament\Resources\Attendances\Tables\AttendancesTable;
use App\Models\Attendance;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Presensi';

    protected static ?string $modelLabel = 'Presensi';

    protected static ?string $pluralModelLabel = 'Presensi';

    public static function canAccess(): bool
    {
        return Gate::allows('attendance.view_all');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
        ];
    }
}
