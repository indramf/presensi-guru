<?php

namespace App\Filament\Resources\SecurityAlerts\Pages;

use App\Enums\AlertStatus;
use App\Filament\Resources\SecurityAlerts\SecurityAlertResource;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Services\SecurityAlertService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ViewSecurityAlert extends ViewRecord
{
    protected static string $resource = SecurityAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                // ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->outlined()
                ->url(SecurityAlertResource::getUrl('index')),

            Action::make('review')
                ->label('Tinjau')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Tinjau Peringatan Anomali')
                ->modalDescription('Periksa peringatan ini, kemudian tentukan status penanganan yang sesuai.')
                ->modalIcon('heroicon-o-shield-exclamation')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Simpan Peninjauan')
                ->modalCancelActionLabel('Batal')
                ->schema([
                    Select::make('status')
                        ->label('Status Penanganan')
                        ->placeholder('Pilih status tujuan')
                        ->options(
                            collect($this->record->status->allowedTransitions())
                                ->mapWithKeys(fn(AlertStatus $status): array => [
                                    $status->value => $status->label(),
                                ])
                                ->all()
                        )
                        ->required()
                        ->native(false),

                    Textarea::make('note')
                        ->label('Catatan Peninjauan')
                        ->placeholder('Tuliskan hasil pemeriksaan atau alasan perubahan status...')
                        ->helperText('Catatan membantu petugas lain memahami hasil peninjauan.')
                        ->rows(4)
                        ->maxLength(1000),
                ])
                ->visible(fn(SecurityAlert $record): bool => auth()->user()?->can('update', $record)
                    && $record->status->allowedTransitions() !== [])
                ->action(function (SecurityAlert $record, array $data): void {
                    try {
                        $actor = auth()->user();

                        if (! $actor instanceof User) {
                            throw new AuthorizationException('Anda tidak berwenang meninjau peringatan.');
                        }

                        $status = AlertStatus::tryFrom($data['status'] ?? '');

                        if ($status === null) {
                            throw ValidationException::withMessages([
                                'status' => 'Status tujuan tidak valid.',
                            ]);
                        }

                        app(SecurityAlertService::class)->changeStatus(
                            actor: $actor,
                            alert: $record,
                            new: $status,
                            note: $data['note'] ?? null,
                        );

                        Notification::make()
                            ->success()
                            ->title('Peringatan berhasil ditinjau')
                            ->send();
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->warning()
                            ->title('Gagal meninjau peringatan')
                            ->body(collect($e->errors())->flatten()->implode(' '))
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
