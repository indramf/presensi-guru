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

        if ($user?->can('attendance.record')) {
            $this->redirect(MyAttendance::getUrl());

            return;
        }

        if (
            $user?->hasRole(\App\Enums\UserRole::Administrator->value)
            || $user?->hasRole(\App\Enums\UserRole::GuruPiket->value)
        ) {
            $this->redirect(ScheduleBoard::getUrl());
        }
    }
}
