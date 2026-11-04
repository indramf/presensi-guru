<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
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
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('actor.name')
                    ->label('Pelaku')
                    ->placeholder('Sistem')
                    ->searchable(),
                TextColumn::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->formatStateUsing(fn (AuditEventType $state): string => $state->value)
                    ->searchable(),
                TextColumn::make('target_type')
                    ->label('Objek')
                    ->state(fn (AuditLog $record): string => $record->target_type
                        ? $record->target_type.($record->target_id ? ' #'.$record->target_id : '')
                        : '-'),
                TextColumn::make('action_result')
                    ->label('Hasil')
                    ->badge()
                    ->formatStateUsing(fn (AuditResult $state): string => match ($state) {
                        AuditResult::Success => 'Berhasil',
                        AuditResult::Denied => 'Ditolak',
                        AuditResult::Failed => 'Gagal',
                    })
                    ->color(fn (AuditResult $state): string => match ($state) {
                        AuditResult::Success => 'success',
                        AuditResult::Denied => 'warning',
                        AuditResult::Failed => 'danger',
                    }),
                TextColumn::make('entry_hash')
                    ->label('Hash')
                    ->formatStateUsing(fn (string $state): string => substr($state, 0, 8)),
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
                        ->mapWithKeys(fn (AuditEventType $event): array => [$event->value => $event->value])
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
                ViewAction::make(),
            ])
            ->defaultSort('occurred_at', 'desc');
    }
}
