<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    protected static ?string $title = 'Beranda';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.role-dashboard';

    public function mount(): void
    {
        $user = auth()->user();

        $target = match (true) {
            $user?->can('dashboard.view') => Monitoring::getUrl(),
            $user?->can('attendance.record') => MyAttendance::getUrl(),
            default => null,
        };

        if ($target !== null) {
            $this->redirect($target);
        }
    }
}
