<?php

namespace App\Filament\Resources\Attendances;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\Schemas\AttendanceInfolist;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Presensi';

    protected static ?string $modelLabel = 'Presensi';

    protected static ?string $pluralModelLabel = 'Presensi';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', Attendance::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceInfolist::configure($schema);
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
                Tables\Columns\TextColumn::make('teacher.full_name')
                    ->label('Guru')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule.schoolClass.class_name')
                    ->label('Kelas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule.subject.subject_name')
                    ->label('Mata Pelajaran'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AttendanceStatus $state): string => match ($state) {
                        AttendanceStatus::Hadir => 'Hadir',
                        AttendanceStatus::Terlambat => 'Terlambat',
                        AttendanceStatus::Izin => 'Izin',
                        AttendanceStatus::TidakHadir => 'Tidak hadir',
                    })
                    ->icon(fn (AttendanceStatus $state): string => match ($state) {
                        AttendanceStatus::Hadir => 'heroicon-o-check-circle',
                        AttendanceStatus::Terlambat => 'heroicon-o-clock',
                        AttendanceStatus::Izin => 'heroicon-o-document-text',
                        AttendanceStatus::TidakHadir => 'heroicon-o-x-circle',
                    })
                    ->color(fn (AttendanceStatus $state): string => match ($state) {
                        AttendanceStatus::Hadir => 'success',
                        AttendanceStatus::Terlambat => 'warning',
                        AttendanceStatus::Izin => 'info',
                        AttendanceStatus::TidakHadir => 'danger',
                    }),
                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Dicatat Oleh'),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verifikasi')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Terverifikasi' : 'Belum terverifikasi')
                    ->icon(fn ($state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-question-mark-circle')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('attendance_date', now('Asia/Jakarta')))
                    ->default(),
                SelectFilter::make('teacher_id')
                    ->relationship('teacher', 'full_name')
                    ->label('Guru'),
                SelectFilter::make('class_id')
                    ->relationship('schedule.schoolClass', 'class_name')
                    ->label('Kelas'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AttendanceStatus::class),
            ])
            ->emptyStateHeading('Belum ada presensi')
            ->emptyStateDescription('Belum ada data presensi yang sesuai dengan pilihan Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->recordActions([
                ViewAction::make(),

                Action::make('verify')
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

                Action::make('change_status')
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['teacher', 'schedule.schoolClass', 'schedule.subject', 'recorder', 'verifier']);

        $user = auth()->user();

        if ($user && ! $user->can('attendance.view_all')) {
            $query->whereHas('teacher', fn (Builder $teacherQuery): Builder => $teacherQuery
                ->where('user_id', $user->id));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
        ];
    }
}
