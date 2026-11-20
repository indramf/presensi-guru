<?php

namespace App\Filament\Resources\Subjects\Tables;

use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('subject_name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn(bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('subject_name')
            ->searchPlaceholder('Cari mata pelajaran...')
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
                        ->label(fn(Subject $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn(Subject $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn(Subject $record): string => $record->is_active ? 'danger' : 'success')
                        ->visible(fn(Subject $record): bool => Gate::allows('deactivate', $record))
                        ->requiresConfirmation()
                        ->modalHeading(fn(Subject $record): string => $record->is_active ? 'Nonaktifkan Mata Pelajaran?' : 'Aktifkan Mata Pelajaran?')
                        ->modalDescription(fn(Subject $record): string => $record->is_active
                            ? "Apakah kamu yakin ingin menonaktifkan mata pelajaran {$record->subject_name}?"
                            : "Apakah kamu yakin ingin mengaktifkan kembali mata pelajaran {$record->subject_name}?")
                        ->action(function (Subject $record): void {
                            Gate::authorize('deactivate', $record);

                            $newStatus = ! $record->is_active;

                            $record->update([
                                'is_active' => $newStatus,
                            ]);

                            Notification::make()
                                ->title($newStatus ? 'Mata pelajaran berhasil diaktifkan' : 'Mata pelajaran berhasil dinonaktifkan')
                                ->success()
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->emptyStateHeading('Belum ada mata pelajaran')
            ->emptyStateDescription('Mata pelajaran akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-book-open');
    }
}
