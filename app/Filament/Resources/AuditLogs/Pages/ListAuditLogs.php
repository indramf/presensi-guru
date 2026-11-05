<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditVerificationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Auth\Access\AuthorizationException;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verifyIntegrity')
                ->label('Periksa integritas')
                ->color('gray')
                ->visible(fn (): bool => auth()->user()?->can('verify', AuditLog::class) === true)
                ->action(function (): void {
                    try {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new AuthorizationException('Anda tidak berwenang memeriksa integritas audit log.');
                        }

                        $result = app(AuditVerificationService::class)->run($actor);

                        if ($result['ok']) {
                            Notification::make()
                                ->success()
                                ->title('Rantai audit log valid')
                                ->body("{$result['checked']} entri diperiksa dan sesuai checkpoint.")
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title('Ditemukan ketidaksesuaian')
                            ->body(collect($result['problems'])
                                ->map(fn (string $problem): string => '- '.$problem)
                                ->implode("\n"))
                            ->persistent()
                            ->send();
                    } catch (AuthorizationException $e) {
                        Notification::make()
                            ->danger()
                            ->title('Tidak diizinkan')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
