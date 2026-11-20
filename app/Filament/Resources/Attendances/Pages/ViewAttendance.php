<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\Attendances\Schemas\AttendanceInfolist;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ViewAttendance extends ViewRecord
{
    protected static string $resource = AttendanceResource::class;

    public function infolist(Schema $schema): Schema
    {
        return AttendanceInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(AttendanceResource::getUrl('index')),

            Action::make('change_status')
                ->label('Ubah Status')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Attendance $record) => Gate::allows('update', $record))
                ->form(AttendanceForm::statusChange())
                ->action(function (array $data, Attendance $record) {
                    try {
                        $newStatus = $data['status'] instanceof AttendanceStatus
                            ? $data['status']
                            : AttendanceStatus::from($data['status']);

                        app(AttendanceService::class)->changeStatus(
                            Auth::user(),
                            $record,
                            $newStatus,
                            $data['reason']
                        );

                        Notification::make()
                            ->success()
                            ->title('Status berhasil diubah')
                            ->send();

                    } catch (ValidationException $e) {
                        Notification::make()
                            ->danger()
                            ->title('Validasi Gagal')
                            ->body(collect($e->errors())->first()[0])
                            ->send();

                    } catch (AuthorizationException $e) {
                        Notification::make()
                            ->danger()
                            ->title('Akses Ditolak')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
