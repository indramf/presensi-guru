<?php

namespace App\Filament\Resources\Teachers\Tables;

use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search) {
                            $query->where('full_name', 'like', "%{$search}%")
                                ->orWhere('teacher_code', 'like', "%{$search}%");
                        });
                    })
                    ->searchable(),
                TextColumn::make('subjects')
                    ->label('Mata Pelajaran')
                    ->state(fn (Teacher $record): array => $record->schedules
                        ->pluck('subject.subject_name')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all())
                    ->badge()
                    ->limitList(2)
                    ->expandableLimitedList(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->searchPlaceholder('Cari Guru...')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),

                    EditAction::make()
                        ->label('Ubah'),

                    Action::make('toggle_active')
                        ->label(fn (Teacher $record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (Teacher $record): string => $record->is_active
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle')
                        ->color(fn (Teacher $record): string => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn (Teacher $record): string => $record->is_active
                            ? 'Nonaktifkan guru?'
                            : 'Aktifkan guru?')
                        ->modalDescription(fn (Teacher $record): string => $record->is_active
                            ? 'Guru tidak akan digunakan untuk aktivitas baru. Riwayat data tetap tersimpan.'
                            : 'Guru akan diaktifkan kembali dan dapat digunakan dalam aktivitas baru.')
                        ->modalSubmitActionLabel(fn (Teacher $record): string => $record->is_active
                            ? 'Ya, nonaktifkan'
                            : 'Ya, aktifkan')
                        ->visible(fn (Teacher $record): bool => Gate::allows('deactivate', $record))
                        ->action(function (Teacher $record): void {
                            Gate::authorize('deactivate', $record);

                            $isActive = ! $record->is_active;

                            $record->update([
                                'is_active' => $isActive,
                            ]);

                            Notification::make()
                                ->success()
                                ->title($isActive
                                    ? 'Guru berhasil diaktifkan'
                                    : 'Guru berhasil dinonaktifkan')
                                ->send();
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->emptyStateHeading('Belum ada guru')
            ->emptyStateDescription('Data guru akan tampil di sini setelah ditambahkan.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
