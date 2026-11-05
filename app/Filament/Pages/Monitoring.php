<?php

namespace App\Filament\Pages;

use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Services\ClassReportService;
use App\Services\MonitoringService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class Monitoring extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Monitoring';

    protected static ?string $slug = 'monitoring';

    protected static ?int $navigationSort = 1;

    // Filament v4: $view TIDAK static
    protected string $view = 'filament.pages.monitoring';

    public function getTitle(): string|Htmlable
    {
        return 'Monitoring Kelas — '.now('Asia/Jakarta')->locale('id')->translatedFormat('l, d M Y');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('dashboard.view') ?? false;
    }

    /**
     * Di blade: $this->rows
     */
    #[Computed]
    public function rows(): Collection
    {
        $now = now('Asia/Jakarta');

        return app(MonitoringService::class)
            ->sessionsFor($now, $now->copy())
            ->sortBy(fn ($row): int => $row->needsAction ? 0 : 1)
            ->values();
    }

    /**
     * Di blade: $this->summary
     */
    #[Computed]
    public function summary(): array
    {
        return app(MonitoringService::class)->summary($this->rows);
    }

    /**
     * Aksi Modal: Konfirmasi Kelas oleh Guru Piket (FR-10)
     */
    public function reportAction(): Action
    {
        return Action::make('report')
            ->label('Konfirmasi Kelas')
            ->color('primary')
            ->size(Size::Medium)
            ->visible(fn (): bool => auth()->user()->can('create', ClassReport::class))
            ->schema([
                Select::make('report_status')
                    ->label('Status Kelas')
                    ->options(
                        collect(ClassReportStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required()
                    ->live(),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->helperText('Minimal 5 karakter, kecuali jika statusnya Guru Hadir.')
                    ->placeholder('Contoh: Guru belum hadir saat sesi dimulai.')
                    ->rows(3),
            ])
            ->action(function (array $data, array $arguments): void {
                $schedule = Schedule::find($arguments['schedule_id'] ?? null);

                if (! $schedule) {
                    Notification::make()->danger()->title('Jadwal tidak ditemukan')->send();

                    return;
                }

                try {
                    app(ClassReportService::class)->create(
                        reporter: auth()->user(),
                        schedule: $schedule,
                        status: ClassReportStatus::from($data['report_status']),
                        description: $data['description'] ?? null
                    );

                    Notification::make()->success()->title('Laporan kelas berhasil disimpan')->send();
                } catch (ValidationException $e) {
                    Notification::make()->warning()->title('Gagal menyimpan laporan')
                        ->body(collect($e->errors())->flatten()->implode(' '))
                        ->send();
                } catch (AuthorizationException $e) {
                    Notification::make()->danger()->title('Tidak diizinkan')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    /**
     * Aksi Modal: Perbarui Tindak Lanjut Laporan Kelas
     */
    public function updateFollowUpAction(): Action
    {
        return Action::make('updateFollowUp')
            ->label('Tindak Lanjut')
            ->color('gray')
            ->size(Size::Medium)
            ->requiresConfirmation()
            ->modalHeading('Perbarui tindak lanjut')
            ->modalDescription('Pastikan status tindak lanjut yang dipilih sudah sesuai.')
            ->mountUsing(function (Schema $schema, array $arguments): void {
                $schema->fill([
                    'follow_up_status' => $arguments['current_status'] ?? FollowUpStatus::Belum->value,
                ]);
            })
            ->schema([
                Select::make('follow_up_status')
                    ->label('Status Tindak Lanjut')
                    ->options(
                        collect(FollowUpStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required(),
                Textarea::make('note')
                    ->label('Catatan Tambahan')
                    ->rows(2),
            ])
            ->action(function (array $data, array $arguments): void {
                $report = ClassReport::find($arguments['report_id'] ?? null);

                if (! $report) {
                    Notification::make()->danger()->title('Laporan tidak ditemukan')->send();

                    return;
                }

                try {
                    app(ClassReportService::class)->updateFollowUp(
                        actor: auth()->user(),
                        report: $report,
                        status: FollowUpStatus::from($data['follow_up_status']),
                        note: $data['note'] ?? null
                    );

                    Notification::make()->success()->title('Tindak lanjut berhasil diperbarui')->send();
                } catch (ValidationException $e) {
                    Notification::make()->warning()->title('Gagal memperbarui')
                        ->body(collect($e->errors())->flatten()->implode(' '))
                        ->send();
                } catch (AuthorizationException $e) {
                    Notification::make()->danger()->title('Tidak diizinkan')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}
