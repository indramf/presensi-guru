<?php

namespace App\Filament\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Presensi';

    protected static ?string $modelLabel = 'Presensi';

    protected static ?string $pluralModelLabel = 'Presensi';

    public static function canAccess(): bool
    {
        return true; // Set true untuk memastikannya tampil saat pengujian lokal
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
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
                Tables\Columns\TextColumn::make('schedule.schoolClass.name')
                    ->label('Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule.subject.name')
                    ->label('Mapel'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('recordedBy.name')
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
                    ->relationship('schedule.schoolClass', 'name')
                    ->label('Kelas'),
                SelectFilter::make('status')
                    ->options(AttendanceStatus::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Attendance $record) => auth()->user()->can('update', $record) && is_null($record->verified_at))
                    ->action(function (Attendance $record) {
                        try {
                            app(AttendanceService::class)->verify(auth()->user(), $record);
                            Notification::make()->success()->title('Presensi berhasil diverifikasi')->send();
                        } catch (AuthorizationException $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),

                Tables\Actions\Action::make('change_status')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Attendance $record) => auth()->user()->can('update', $record))
                    ->form([
                        Select::make('status')
                            ->label('Status Baru')
                            ->options(AttendanceStatus::class)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->placeholder('Minimal 10 karakter...')
                            ->required()
                            ->minLength(10),
                    ])
                    ->action(function (array $data, Attendance $record) {
                        try {
                            $newStatus = AttendanceStatus::from($data['status']);
                            app(AttendanceService::class)->changeStatus(auth()->user(), $record, $newStatus, $data['reason']);
                            Notification::make()->success()->title('Status berhasil diubah')->send();
                        } catch (ValidationException $e) {
                            Notification::make()->danger()->title('Validasi Gagal')->body(collect($e->errors())->first()[0])->send();
                        } catch (AuthorizationException $e) {
                            Notification::make()->danger()->title('Akses Ditolak')->body($e->getMessage())->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
        ];
    }
}
