<?php

namespace App\Filament\Resources\SecurityAlerts;

use App\Enums\AlertStatus;
use App\Filament\Resources\SecurityAlerts\Pages\ListSecurityAlerts;
use App\Filament\Resources\SecurityAlerts\Pages\ViewSecurityAlert;
use App\Filament\Resources\SecurityAlerts\Schemas\SecurityAlertInfolist;
use App\Filament\Resources\SecurityAlerts\Tables\SecurityAlertsTable;
use App\Models\SecurityAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SecurityAlertResource extends Resource
{
    protected static ?string $model = SecurityAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup = 'Keamanan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Peringatan Anomali';

    protected static ?string $pluralModelLabel = 'Peringatan Anomali';

    protected static ?string $navigationLabel = 'Peringatan Anomali';

    public static function infolist(Schema $schema): Schema
    {
        return SecurityAlertInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecurityAlertsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['relatedUser', 'reviewer', 'auditLog']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SecurityAlert::query()
            ->where('status', AlertStatus::Baru->value)
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityAlerts::route('/'),
            'view' => ViewSecurityAlert::route('/{record}'),
        ];
    }
}
