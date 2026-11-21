<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Services\TimetableService;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TeacherSchedule extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.pages.teacher-schedule';

    protected static ?string $navigationLabel = 'Jadwal';

    protected static ?string $title = 'Jadwal Saya';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole(UserRole::Guru->value) ?? false;
    }

    public function getSchedule(): array
    {
        $user = Auth::user();

        if (! $user || ! $user->teacher) {
            return [
                'rows' => [],
                'days' => app(TimetableService::class)->days(),
                'todayIndex' => null,
                'unmatched' => [],
            ];
        }

        return app(TimetableService::class)->forTeacher(
            $user->teacher,
            now('Asia/Jakarta'),
        );
    }
}
