<?php

namespace App\Filament\Pages;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;

class ReportPresensi extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Report Presensi';

    protected static ?string $slug = 'report-presensi';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.report-presensi';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?string $teacherId = null;

    public ?string $classId = null;

    public ?string $status = null;

    public function getTitle(): string
    {
        return 'Laporan Presensi Guru';
    }

    public static function canAccess(): bool
    {
        return Gate::allows('attendance_reports.view');
    }

    #[Computed]
    public function rows(): Collection
    {
        return Attendance::query()
            ->with([
                'teacher',
                'schedule.schoolClass',
                'schedule.subject',
            ])
            ->when(
                $this->dateFrom,
                fn(Builder $query) =>
                $query->whereDate('attendance_date', '>=', $this->dateFrom)
            )
            ->when(
                $this->dateTo,
                fn(Builder $query) =>
                $query->whereDate('attendance_date', '<=', $this->dateTo)
            )
            ->when(
                $this->teacherId,
                fn(Builder $query) =>
                $query->where('teacher_id', $this->teacherId)
            )
            ->when(
                $this->classId,
                fn(Builder $query) =>
                $query->whereHas(
                    'schedule',
                    fn(Builder $scheduleQuery) =>
                    $scheduleQuery->where('class_id', $this->classId)
                )
            )
            ->when(
                $this->status,
                fn(Builder $query) =>
                $query->where('status', $this->status)
            )
            ->orderByDesc('attendance_date')
            ->orderByDesc('recorded_at')
            ->get();
    }

    #[Computed]
    public function teachers(): Collection
    {
        return Teacher::query()
            ->orderBy('full_name')
            ->get();
    }

    #[Computed]
    public function classes(): Collection
    {
        return SchoolClass::query()
            ->orderBy('class_name')
            ->get();
    }

    public function statusOptions(): array
    {
        return collect(AttendanceStatus::cases())
            ->mapWithKeys(fn(AttendanceStatus $status) => [
                $status->value => match ($status) {
                    AttendanceStatus::Hadir => 'Hadir',
                    AttendanceStatus::Terlambat => 'Terlambat',
                    AttendanceStatus::Izin => 'Izin',
                    AttendanceStatus::TidakHadir => 'Tidak Hadir',
                },
            ])
            ->all();
    }

    public function exportPdf()
    {
        Gate::authorize('attendance_reports.export');

        $pdf = Pdf::loadView('filament.pdf.report-presensi', [
            'rows' => $this->rows,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'teacherId' => $this->teacherId,
            'teacherName' => $this->teacherId
                ? Teacher::find($this->teacherId)?->full_name
                : null,
            'className' => $this->classId
                ? SchoolClass::find($this->classId)?->class_name
                : null,
            'classId' => $this->classId,
            'status' => $this->status,
        ])->setPaper('a4', 'potret');

        $filename = 'laporan-presensi-' . now()->format('Y-m-d') . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}
