<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class SchoolClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('class_name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->searchPlaceholder('Cari Kelas...')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),

                    EditAction::make()
                        ->label('Ubah'),

                    Action::make('toggleActive')
                        ->label(fn (SchoolClass $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (SchoolClass $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (SchoolClass $record): string => $record->is_active ? 'danger' : 'success')
                        ->visible(fn (SchoolClass $record): bool => Gate::allows('deactivate', $record))
                        ->requiresConfirmation()
                        ->modalHeading(fn (SchoolClass $record): string => $record->is_active ? 'Nonaktifkan Kelas?' : 'Aktifkan Kelas?')
                        ->modalDescription(fn (SchoolClass $record): string => $record->is_active
                            ? "Apakah kamu yakin ingin menonaktifkan kelas {$record->class_name}?"
                            : "Apakah kamu yakin ingin mengaktifkan kembali kelas {$record->class_name}?")
                        ->action(function (SchoolClass $record): void {
                            Gate::authorize('deactivate', $record);

                            $newStatus = ! $record->is_active;

                            $record->update([
                                'is_active' => $newStatus,
                            ]);

                            Notification::make()
                                ->title($newStatus ? 'Kelas berhasil diaktifkan' : 'Kelas berhasil dinonaktifkan')
                                ->success()
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->emptyStateHeading('Belum ada kelas')
            ->emptyStateDescription('Kelas akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-building-office');
    }
}
