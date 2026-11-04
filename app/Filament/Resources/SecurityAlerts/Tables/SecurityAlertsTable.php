<?php

namespace App\Filament\Resources\SecurityAlerts\Tables;

use App\Enums\AlertSeverity;
use App\Enums\AlertStatus;
use App\Enums\AnomalyRule;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Services\SecurityAlertService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class SecurityAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('detected_at')
                    ->label('Waktu Terdeteksi')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('rule_code')
                    ->label('Kode Aturan')
                    ->formatStateUsing(fn (AnomalyRule $state): string => $state->value)
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('severity')
                    ->label('Tingkat')
                    ->badge()
                    ->formatStateUsing(fn (AlertSeverity $state): string => $state->label())
                    ->color(fn (AlertSeverity $state): string => $state->color()),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AlertStatus $state): string => $state->label())
                    ->color(fn (AlertStatus $state): string => match ($state) {
                        AlertStatus::Baru => 'gray',
                        AlertStatus::Ditinjau => 'warning',
                        AlertStatus::Ditutup => 'success',
                    }),
                TextColumn::make('relatedUser.name')
                    ->label('Pengguna Terkait')
                    ->placeholder('Tidak diketahui')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions())
                    ->default(AlertStatus::Baru->value),
                SelectFilter::make('severity')
                    ->label('Tingkat')
                    ->options(self::severityOptions()),
                SelectFilter::make('rule_code')
                    ->label('Kode Aturan')
                    ->options(self::ruleOptions()),
                Filter::make('detected_at')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('detected_at', '>=', $date),
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('detected_at', '<=', $date),
                        )),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('review')
                    ->label('Tinjau')
                    ->color('primary')
                    ->schema(fn (SecurityAlert $record): array => [
                        Select::make('status')
                            ->label('Status Tujuan')
                            ->options(collect($record->status->allowedTransitions())
                                ->mapWithKeys(fn (AlertStatus $status): array => [$status->value => $status->label()])
                                ->all())
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(3),
                    ])
                    ->visible(fn (SecurityAlert $record): bool => auth()->user()?->can('update', $record)
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
            ])
            ->defaultSort('detected_at', 'desc');
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return collect(AlertStatus::cases())
            ->mapWithKeys(fn (AlertStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function severityOptions(): array
    {
        return collect(AlertSeverity::cases())
            ->mapWithKeys(fn (AlertSeverity $severity): array => [$severity->value => $severity->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function ruleOptions(): array
    {
        return collect(AnomalyRule::cases())
            ->mapWithKeys(fn (AnomalyRule $rule): array => [$rule->value => $rule->value])
            ->all();
    }
}
