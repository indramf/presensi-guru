<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => UserRole::tryFrom($state)?->label() ?? $state),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn(bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->searchPlaceholder('Cari nama atau email...')
            ->filters([
                SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),

                    EditAction::make()
                        ->label('Ubah'),

                    Action::make('resetPassword')
                        ->label('Kirim Link Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->visible(fn(User $record): bool => Gate::allows('resetPassword', $record))
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Link Reset Password?')
                        ->modalDescription(
                            fn(User $record): string =>
                            "Tautan reset password akan dikirim ke {$record->email}."
                        )
                        ->action(function (User $record): void {
                            $status = Password::sendResetLink([
                                'email' => $record->email,
                            ]);

                            if ($status === Password::RESET_LINK_SENT) {
                                Notification::make()
                                    ->title('Link reset password berhasil dikirim')
                                    ->body("Email tujuan: {$record->email}")
                                    ->success()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Gagal mengirim link reset password')
                                ->body(__($status))
                                ->danger()
                                ->send();
                        }),

                    Action::make('toggleActive')
                        ->label(fn(User $record): string => $record->is_active
                            ? 'Nonaktifkan'
                            : 'Aktifkan')
                        ->icon(fn(User $record): string => $record->is_active
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle')
                        ->color(fn(User $record): string => $record->is_active
                            ? 'danger'
                            : 'success')
                        ->visible(fn(User $record): bool => Gate::allows('deactivate', $record))
                        ->requiresConfirmation()
                        ->modalHeading(fn(User $record): string => $record->is_active
                            ? 'Nonaktifkan Pengguna?'
                            : 'Aktifkan Pengguna?')
                        ->modalDescription(fn(User $record): string => $record->is_active
                            ? 'Apakah kamu yakin ingin menonaktifkan pengguna ini?'
                            : 'Apakah kamu yakin ingin mengaktifkan kembali pengguna ini?')
                        ->action(function (User $record): void {
                            Gate::authorize('deactivate', $record);

                            $newStatus = ! $record->is_active;

                            $record->update([
                                'is_active' => $newStatus,
                            ]);

                            Notification::make()
                                ->title($newStatus
                                    ? 'Pengguna berhasil diaktifkan'
                                    : 'Pengguna berhasil dinonaktifkan')
                                ->success()
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Pengguna akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
