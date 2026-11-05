<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AuditEventType;
use App\Filament\Resources\Concerns\ConfirmsDeactivation;
use App\Filament\Resources\Users\UserResource;
use App\Services\AuditLogger;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    use ConfirmsDeactivation;

    protected static string $resource = UserResource::class;

    // Sengaja kosong: tanpa aksi hapus (data dinonaktifkan, bukan dihapus).
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Mengisi pilihan peran saat form ubah dibuka.
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->getRoleNames()->first();

        return $data;
    }

    // Penjaga di sisi server. Toggle yang terkunci di UI bukan kontrol keamanan.
    protected function beforeSave(): void
    {
        if (! $this->record->is(Auth::user())) {
            return;
        }

        $deactivating = array_key_exists('is_active', $this->data) && ! $this->data['is_active'];
        $changingRole = ($this->data['role'] ?? null) !== $this->record->getRoleNames()->first();

        if ($deactivating || $changingRole) {
            Notification::make()
                ->danger()
                ->title('Perubahan ditolak')
                ->body('Anda tidak dapat menonaktifkan akun atau mengubah peran Anda sendiri.')
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        $role = $this->data['role'] ?? null;

        if ($role === null || $this->record->hasRole($role)) {
            return;
        }

        $before = $this->record->getRoleNames()->all();

        $this->record->syncRoles([$role]);

        app(AuditLogger::class)->log(
            AuditEventType::DataUpdated,
            actorId: (int) Auth::id(),
            targetType: 'User',
            targetId: $this->record->id,
            before: ['roles' => $before],
            after: ['roles' => [$role]],
        );
    }
}
