<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID Entri'),
                TextEntry::make('occurred_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s'),
                TextEntry::make('actor.name')
                    ->label('Pelaku')
                    ->placeholder('Sistem'),
                TextEntry::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->formatStateUsing(fn (AuditEventType $state): string => $state->value),
                TextEntry::make('target_type')
                    ->label('Jenis Objek')
                    ->placeholder('-'),
                TextEntry::make('target_id')
                    ->label('ID Objek')
                    ->placeholder('-'),
                TextEntry::make('action_result')
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
                TextEntry::make('before_data')
                    ->label('Data Sebelum')
                    ->state(fn (AuditLog $record): string => self::formatData($record->before_data))
                    ->placeholder('Tidak ada data')
                    ->columnSpanFull(),
                TextEntry::make('after_data')
                    ->label('Data Sesudah')
                    ->state(fn (AuditLog $record): string => self::formatData($record->after_data))
                    ->placeholder('Tidak ada data')
                    ->columnSpanFull(),
                TextEntry::make('reason')
                    ->label('Alasan')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('previous_hash')
                    ->label('Hash Sebelumnya')
                    ->placeholder('Entri pertama')
                    ->columnSpanFull(),
                TextEntry::make('entry_hash')
                    ->label('Hash Entri')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    private static function formatData(?array $data): string
    {
        if ($data === null || $data === []) {
            return 'Tidak ada data';
        }

        return implode(' · ', self::flattenData($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private static function flattenData(array $data, string $prefix = ''): array
    {
        $lines = [];

        foreach ($data as $key => $value) {
            $label = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $value !== []) {
                array_push($lines, ...self::flattenData($value, $label));

                continue;
            }

            $formattedValue = match (true) {
                is_bool($value) => $value ? 'Ya' : 'Tidak',
                $value === null => '-',
                is_scalar($value) => (string) $value,
                default => '-',
            };

            $lines[] = $label.': '.$formattedValue;
        }

        return $lines;
    }
}
