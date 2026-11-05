<?php

namespace App\Filament\Pages;

use App\Exceptions\AttendanceRejectedException;
use App\Models\Schedule;
use App\Services\AttendanceService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MyAttendance extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected string $view = 'filament.pages.my-attendance';

    protected static ?string $navigationLabel = 'Presensi Saya';

    protected static ?string $title = 'Presensi Saya';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('attendance.record') ?? false;
    }

    public function getSchedulesProperty(): Collection
    {
        $user = auth()->user();

        if (! $user || ! $user->teacher) {
            return collect();
        }

        $today = now('Asia/Jakarta');

        return Schedule::active()
            ->where('teacher_id', $user->teacher->id)
            ->where('day_of_week', $today->dayOfWeekIso)
            ->orderBy('start_time')
            ->with(['schoolClass', 'subject', 'attendances' => function ($query) use ($today) {
                $query->whereDate('attendance_date', $today->toDateString());
            }])
            ->get();
    }

    public function recordAttendance(int $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $attendanceService = app(AttendanceService::class);

        try {
            $attendanceService->record(auth()->user(), $schedule, now('Asia/Jakarta'));

            Notification::make()
                ->success()
                ->title('Presensi tersimpan')
                ->send();

        } catch (AttendanceRejectedException $e) {
            Notification::make()
                ->warning()
                ->title('Presensi tidak dapat disimpan')
                ->body($e->getMessage())
                ->send();
        }
    }
}
