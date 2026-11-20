<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aktivitas')
                    ->description('Informasi dasar aktivitas yang tercatat dalam sistem.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID Entri')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('occurred_at')
                                    ->label('Waktu Aktivitas')
                                    ->dateTime('d M Y, H:i:s', 'Asia/Jakarta')
                                    ->icon('heroicon-o-clock'),

                                TextEntry::make('actor.name')
                                    ->label('Pelaku')
                                    ->placeholder('Sistem')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('event_type')
                                    ->label('Jenis Peristiwa')
                                    ->formatStateUsing(
                                        fn (AuditEventType $state): string => str_replace('.', ' / ', $state->value)
                                    )
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('target_type')
                                    ->label('Jenis Objek')
                                    ->formatStateUsing(
                                        fn (?string $state): string => $state
                                            ? class_basename($state)
                                            : '-'
                                    )
                                    ->placeholder('-'),

                                TextEntry::make('target_id')
                                    ->label('ID Objek')
                                    ->placeholder('-'),

                                TextEntry::make('action_result')
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
                                    }),
                            ]),
                    ]),

                Section::make('Detail Perubahan')
                    ->description('Rincian data yang berubah ketika aktivitas dilakukan.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
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
                    ]),

                Section::make('Integritas Audit')
                    ->description('Informasi hash untuk memeriksa integritas rantai audit.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextEntry::make('previous_hash')
                            ->label('Hash Sebelumnya')
                            ->placeholder('Entri pertama')
                            ->copyable()
                            ->columnSpanFull(),

                        TextEntry::make('entry_hash')
                            ->label('Hash Entri')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
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
