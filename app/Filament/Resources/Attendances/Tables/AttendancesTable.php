<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Filament\Actions\Action;
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
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule.start_time')
                    ->label('Jam')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.user.name')
                    ->label('Guru')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule.schoolClass.class_name')
                    ->label('Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule.subject.name')
                    ->label('Mapel'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Dicatat Oleh'),
                Tables\Columns\IconColumn::make('verified_at')
                    ->label('Verifikasi')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
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
                ViewAction::make(),

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
                            $newStatus = AttendanceStatus::from($data['status']);
                            app(AttendanceService::class)->changeStatus(Auth::user(), $record, $newStatus, $data['reason']);
                            Notification::make()->success()->title('Status berhasil diubah')->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Validasi Gagal')->body(collect($e->errors())->first()[0])->send();
                        } catch (AuthorizationException $e) {
                            Notification::make()->danger()->title('Akses Ditolak')->body($e->getMessage())->send();
                        }
                    }),
            ]);
    }
}
