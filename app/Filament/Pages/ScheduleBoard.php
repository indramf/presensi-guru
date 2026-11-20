<?php

namespace App\Filament\Pages;

use App\Services\TimetableService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;

class ScheduleBoard extends Page
{
    protected string $view = 'filament.pages.schedule-board';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    public string $viewMode = 'all';

    public ?int $selectedClassId = null;

    public int $selectedDay;

    #[Url]
    public string $mode = 'day';   // all, class, atau day

    #[Url]
    public $classId = null;

    #[Url]
    public $day = null;

    public static function canAccess(): bool
    {
        return Gate::allows('dashboard.view') ?? false;
    }

    public function mount(): void
    {
        if (! in_array($this->mode, ['all', 'class', 'day'], true)) {
            $this->mode = 'day';
        }

        if (empty($this->day)) {
            $isoDays = array_column(app(TimetableService::class)->days(), 'iso');
            $today = now()->dayOfWeekIso;

            $this->day = in_array($today, $isoDays, true)
                ? $today
                : ($isoDays[0] ?? null);
        }
    }

    public function getClasses(): Collection
    {
        return app(TimetableService::class)->classes();
    }

    public function getDays(): array
    {
        return app(TimetableService::class)->days();
    }

    public function getBoard(): array
    {
        $service = app(TimetableService::class);

        if ($this->mode === 'class') {
            $class = $this->classId ? $service->classes()->firstWhere('id', (int) $this->classId) : null;

            return ['mode' => 'class', 'class' => $class] + ($class ? $service->forClass($class, now()) : []);
        }

        if ($this->mode === 'day') {
            $day = (int) $this->day;
            $valid = in_array($day, array_column($service->days(), 'iso'), true);
            $isToday = $day === now()->dayOfWeekIso;

            return ['mode' => 'day', 'valid' => $valid, 'dayName' => $service->dayName($day), 'isToday' => $isToday]
                + ($valid ? $service->forDay($day, $isToday ? now() : null) : []);
        }

        return ['mode' => 'all'] + $service->forAllClasses(now());
    }
}
