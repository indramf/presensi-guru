<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Waktu Aktivitas')
                    ->dateTime('d M Y H:i:s', 'Asia/Jakarta')
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                TextColumn::make('actor.name')
                    ->label('Pelaku')
                    ->placeholder('Sistem')
                    ->icon('heroicon-o-user')
                    ->searchable(),

                TextColumn::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->badge()
                    ->formatStateUsing(fn (AuditEventType $state): string => str_replace('.', ' / ', $state->value))
                    ->color(fn (AuditEventType $state): string => match ($state) {
                        AuditEventType::LoginFailed,
                        AuditEventType::AccessDenied,
                        AuditEventType::AttendanceRejected => 'danger',

                        AuditEventType::DataCreated,
                        AuditEventType::AttendanceRecorded,
                        AuditEventType::ClassReportCreated,
                        AuditEventType::AttendanceVerified => 'success',

                        AuditEventType::DataDeactivated,
                        AuditEventType::AttendanceStatusChanged,
                        AuditEventType::ClassReportFollowUpChanged => 'warning',

                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('target_type')
                    ->label('Objek')
                    ->state(fn (AuditLog $record): string => $record->target_type
                        ? class_basename($record->target_type).($record->target_id ? ' #'.$record->target_id : '')
                        : 'Tidak ada objek')
                    ->placeholder('Tidak ada objek')
                    ->icon('heroicon-o-rectangle-stack')
                    ->searchable(),

                TextColumn::make('action_result')
                    ->label('Hasil Aktivitas')
                    ->badge()
                    ->formatStateUsing(fn (AuditResult $state): string => match ($state) {
                        AuditResult::Success => 'Berhasil',
                        AuditResult::Denied => 'Ditolak',
                        AuditResult::Failed => 'Gagal',
                    })
                    ->icon(fn (AuditResult $state): string => match ($state) {
                        AuditResult::Success => 'heroicon-o-check-circle',
                        AuditResult::Denied => 'heroicon-o-no-symbol',
                        AuditResult::Failed => 'heroicon-o-x-circle',
                    })
                    ->color(fn (AuditResult $state): string => match ($state) {
                        AuditResult::Success => 'success',
                        AuditResult::Denied => 'warning',
                        AuditResult::Failed => 'danger',
                    })
                    ->sortable(),
            ])
            ->filters([
                Filter::make('occurred_at')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('occurred_at', '>=', $date),
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('occurred_at', '<=', $date),
                        )),

                SelectFilter::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->options(collect(AuditEventType::cases())
                        ->mapWithKeys(fn (AuditEventType $event): array => [
                            $event->value => $event->value,
                        ])
                        ->all()),

                SelectFilter::make('action_result')
                    ->label('Hasil')
                    ->options([
                        AuditResult::Success->value => 'Berhasil',
                        AuditResult::Denied->value => 'Ditolak',
                        AuditResult::Failed->value => 'Gagal',
                    ]),

                SelectFilter::make('actor_user_id')
                    ->label('Pelaku')
                    ->relationship('actor', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->columnToggleFormColumns(0)
            ->defaultSort('occurred_at', 'desc')
            ->emptyStateHeading('Belum ada riwayat audit log')
            ->emptyStateDescription('Aktivitas sistem yang tercatat akan tampil di sini.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
