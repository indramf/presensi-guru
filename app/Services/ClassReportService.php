<?php

namespace App\Services;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ClassReportService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Guru piket mencatat kondisi kelas (FR-10). */
    public function create(
        User $reporter,
        Schedule $schedule,
        ClassReportStatus $status,
        ?string $description = null,
        ?CarbonInterface $at = null,
    ): ClassReport {
        $at ??= now();

        if (Gate::forUser($reporter)->denies('create', ClassReport::class)) {
            $this->deny($reporter, 'Schedule', $schedule->id, 'class_report_create_forbidden');
        }

        if (! $schedule->is_active || $at->dayOfWeekIso !== $schedule->day_of_week) {
            throw ValidationException::withMessages([
                'schedule' => 'Laporan hanya dapat dibuat untuk jadwal aktif hari ini.',
            ]);
        }

        $start = Carbon::parse($at->toDateString() . ' ' . $schedule->start_time, $at->getTimezone());

        if ($at->lt($start)) {
            throw ValidationException::withMessages(['schedule' => 'Sesi ini belum dimulai.']);
        }

        $description = $description === null ? null : trim($description);

        if ($status !== ClassReportStatus::GuruHadir && mb_strlen((string) $description) < 5) {
            throw ValidationException::withMessages([
                'description' => 'Keterangan wajib diisi, minimal 5 karakter.',
            ]);
        }

        // Laporan dan audit log dalam satu transaksi.
        return DB::transaction(function () use ($reporter, $schedule, $status, $description, $at) {
            $report = ClassReport::create([
                'schedule_id' => $schedule->id,
                'report_date' => $at->toDateString(),
                'reported_by' => $reporter->id,
                'report_status' => $status,
                'description' => $description,
                'follow_up_status' => $status === ClassReportStatus::GuruHadir
                    ? FollowUpStatus::Selesai
                    : FollowUpStatus::Belum,
            ]);

            $this->audit->log(
                AuditEventType::ClassReportCreated,
                actorId: $reporter->id,
                targetType: 'ClassReport',
                targetId: $report->id,
                after: [
                    'schedule_id' => $schedule->id,
                    'report_date' => $at->toDateString(),
                    'report_status' => $status->value,
                    'follow_up_status' => $report->follow_up_status->value,
                ],
            );

            return $report;
        });
    }

    /** Memperbarui status tindak lanjut laporan. */
    public function updateFollowUp(User $actor, ClassReport $report, FollowUpStatus $status, ?string $note = null): ClassReport
    {
        if (Gate::forUser($actor)->denies('update', $report)) {
            $this->deny($actor, 'ClassReport', $report->id, 'class_report_update_forbidden');
        }

        return DB::transaction(function () use ($actor, $report, $status, $note) {
            $locked = ClassReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($locked->follow_up_status === $status) {
                throw ValidationException::withMessages([
                    'follow_up_status' => 'Status tindak lanjut sama dengan saat ini.',
                ]);
            }

            $before = ['follow_up_status' => $locked->follow_up_status->value];

            $locked->update(['follow_up_status' => $status]);

            $this->audit->log(
                AuditEventType::ClassReportFollowUpChanged,
                actorId: $actor->id,
                targetType: 'ClassReport',
                targetId: $locked->id,
                before: $before,
                after: ['follow_up_status' => $status->value],
                reason: $note !== null ? trim($note) : null,
            );

            return $locked;
        });
    }

    private function deny(User $user, string $targetType, int $targetId, string $code): never
    {
        $this->audit->log(
            AuditEventType::AccessDenied,
            AuditResult::Denied,
            actorId: $user->id,
            targetType: $targetType,
            targetId: $targetId,
            reason: $code,
        );

        throw new AuthorizationException('Anda tidak berwenang melakukan tindakan ini.');
    }
}
