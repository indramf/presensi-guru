<?php

namespace App\Filament\Resources\SecurityAlerts\Schemas;

use App\Enums\AlertSeverity;
use App\Enums\AlertStatus;
use App\Enums\AnomalyRule;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\SecurityAlert;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SecurityAlertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('rule_code')
                    ->label('Kode Aturan')
                    ->formatStateUsing(fn (AnomalyRule $state): string => $state->value),
                TextEntry::make('rule_description')
                    ->label('Arti Aturan')
                    ->state(fn (SecurityAlert $record): string => $record->rule_code->description()),
                TextEntry::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                TextEntry::make('severity')
                    ->label('Tingkat')
                    ->badge()
                    ->formatStateUsing(fn (AlertSeverity $state): string => $state->label())
                    ->icon(fn (AlertSeverity $state): string => match ($state) {
                        AlertSeverity::Rendah => 'heroicon-o-information-circle',
                        AlertSeverity::Sedang => 'heroicon-o-exclamation-triangle',
                        AlertSeverity::Tinggi => 'heroicon-o-exclamation-circle',
                    })
                    ->color(fn (AlertSeverity $state): string => $state->color()),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AlertStatus $state): string => $state->label())
                    ->icon(fn (AlertStatus $state): string => match ($state) {
                        AlertStatus::Baru => 'heroicon-o-bell-alert',
                        AlertStatus::Ditinjau => 'heroicon-o-eye',
                        AlertStatus::Ditutup => 'heroicon-o-check-circle',
                    })
                    ->color(fn (AlertStatus $state): string => match ($state) {
                        AlertStatus::Baru => 'gray',
                        AlertStatus::Ditinjau => 'warning',
                        AlertStatus::Ditutup => 'success',
                    }),
                TextEntry::make('detected_at')
                    ->label('Waktu Terdeteksi')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta'),
                TextEntry::make('relatedUser.name')
                    ->label('Pengguna Terkait')
                    ->placeholder('Tidak diketahui'),
                TextEntry::make('auditLog.id')
                    ->label('Entri Audit Log Pemicu')
                    ->placeholder('Tidak tersedia')
                    ->url(fn (SecurityAlert $record): ?string => $record->auditLog
                        ? AuditLogResource::getUrl('view', ['record' => $record->auditLog])
                        : null),
                TextEntry::make('reviewer.name')
                    ->label('Ditinjau Oleh')
                    ->placeholder('Belum ditinjau'),
                TextEntry::make('reviewed_at')
                    ->label('Waktu Ditinjau')
                    ->dateTime('d M Y H:i', 'Asia/Jakarta')
                    ->placeholder('Belum ditinjau'),
                TextEntry::make('review_notice')
                    ->label('Catatan')
                    ->state('Peringatan ini adalah petunjuk untuk diperiksa, bukan bukti pelanggaran.')
                    ->columnSpanFull(),
            ]);
    }
}
