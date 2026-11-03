<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AuditEventType;
use App\Filament\Resources\Users\UserResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $role = $this->data['role'];

        $this->record->syncRoles([$role]);

        app(AuditLogger::class)->log(
            AuditEventType::DataUpdated,
            actorId: (int) Auth::id(),
            targetType: 'User',
            targetId: $this->record->id,
            after: ['roles' => [$role]],
        );
    }
}
