<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AttendancesTable
{
    private static function statusLabel(mixed $state): string
    {
        $status = $state instanceof AttendanceStatus
            ? $state
            : AttendanceStatus::tryFrom((string) $state);

        return match ($status) {
            AttendanceStatus::Hadir => 'Hadir',
            AttendanceStatus::Terlambat => 'Terlambat',
            AttendanceStatus::Izin => 'Izin',
            AttendanceStatus::TidakHadir => 'Tidak Hadir',
            default => '-',
        };
    }

    private static function statusColor(mixed $state): string
    {
        $status = $state instanceof AttendanceStatus
            ? $state
            : AttendanceStatus::tryFrom((string) $state);

        return match ($status) {
            AttendanceStatus::Hadir => 'success',
            AttendanceStatus::Terlambat => 'warning',
            AttendanceStatus::Izin => 'info',
            AttendanceStatus::TidakHadir => 'danger',
            default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) => $state
                        ? Carbon::parse($state)->locale('id')->translatedFormat('d F Y')
                        : '-')
                    ->sortable()
                    ->width('125px'),
                Tables\Columns\TextColumn::make('schedule.start_time')
                    ->label('Jam')
                    ->time('H:i')
                    ->sortable()
                    ->width('75px'),
                Tables\Columns\TextColumn::make('teacher.user.name')
                    ->label('Guru')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('schedule.schoolClass.class_name')
                    ->label('Kelas')
                    ->searchable()
                    ->width('90px'),
                Tables\Columns\TextColumn::make('schedule.subject.subject_name')
                    ->label('Mata Pelajaran')
                    ->limit(18)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::statusLabel($state))
                    ->color(fn ($state) => self::statusColor($state))
                    ->width('115px'),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verifikasi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => filled($state)
                        ? 'Terverifikasi'
                        : 'Belum diverifikasi')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->width('145px'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('attendance_date', now()))
                    ->default(),
                SelectFilter::make('teacher_id')
                    ->relationship('teacher.user', 'name')
                    ->label('Guru'),
                SelectFilter::make('class_id')
                    ->relationship('schedule.schoolClass', 'class_name')
                    ->label('Kelas'),
                SelectFilter::make('status')
                    ->options(AttendanceStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),
                    Action::make('verify')
                        ->label('Verifikasi')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Attendance $record) => Gate::allows('update', $record) && is_null($record->verified_at))
                        ->action(function (Attendance $record) {
                            try {
                                app(AttendanceService::class)->verify(Auth::user(), $record);
                                Notification::make()->success()->title('Presensi berhasil diverifikasi')->send();
                            } catch (AuthorizationException $e) {
                                Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                            }
                        }),
                    Action::make('change_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Attendance $record) => Gate::allows('update', $record))
                        ->form(AttendanceForm::statusChange())
                        ->action(function (array $data, Attendance $record) {
                            try {
                                $newStatus = $data['status'] instanceof AttendanceStatus
                                    ? $data['status']
                                    : AttendanceStatus::from($data['status']);
                                app(AttendanceService::class)->changeStatus(Auth::user(), $record, $newStatus, $data['reason']);
                                Notification::make()->success()->title('Status berhasil diubah')->send();
                            } catch (ValidationException $e) {
                                Notification::make()->danger()->title('Validasi Gagal')->body(collect($e->errors())->first()[0])->send();
                            } catch (AuthorizationException $e) {
                                Notification::make()->danger()->title('Akses Ditolak')->body($e->getMessage())->send();
                            }
                        }),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Presensi')
                        ->modalDescription('Apakah kamu yakin ingin menghapus data presensi ini? Tindakan ini tidak dapat dibatalkan.')
                        ->visible(fn (Attendance $record) => Gate::allows('delete', $record)),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray'),
            ]);
    }
}
