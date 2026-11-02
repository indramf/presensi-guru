<?php

namespace App\Services;

use App\Enums\AttendanceRejection;
use App\Enums\AttendanceStatus;
use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Enums\SessionState;
use App\Exceptions\AttendanceRejectedException;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Guru mencatat presensi untuk sesinya (FR-06, FR-07). */
    public function record(User $user, Schedule $schedule, ?CarbonInterface $at = null): Attendance
    {
        $at ??= now();
        $schedule->loadMissing('teacher');

        // Penolakan dicatat SEBELUM/di luar transaksi simpan, supaya log tidak ikut ter-rollback.
        if (! Gate::forUser($user)->allows('create', Attendance::class)
            || $schedule->teacher?->user_id !== $user->id) {
            $this->reject($user, $schedule, $at, AttendanceRejection::NotYourSchedule);
        }

        if (! $schedule->is_active || ! $schedule->teacher->is_active) {
            $this->reject($user, $schedule, $at, AttendanceRejection::InactiveSchedule);
        }

        if ($at->dayOfWeekIso !== $schedule->day_of_week) {
            $this->reject($user, $schedule, $at, AttendanceRejection::WrongDay);
        }

        [$opens, $start, $end] = $this->window($schedule, $at);

        if ($at->lt($opens) || $at->gt($end)) {
            $this->reject($user, $schedule, $at, AttendanceRejection::OutsideWindow);
        }

        $date = $at->toDateString();

        if (Attendance::where('schedule_id', $schedule->id)->whereDate('attendance_date', $date)->exists()) {
            $this->reject($user, $schedule, $at, AttendanceRejection::Duplicate);
        }

        $status = $at->gt($start->copy()->addMinutes((int) config('attendance.late_after_minutes')))
            ? AttendanceStatus::Terlambat
            : AttendanceStatus::Hadir;

        try {
            // Presensi dan audit log dalam SATU transaksi (NFR-04).
            return DB::transaction(function () use ($user, $schedule, $at, $date, $status) {
                $attendance = Attendance::create([
                    'schedule_id' => $schedule->id,
                    'teacher_id' => $schedule->teacher_id,
                    'attendance_date' => $date,
                    'status' => $status,
                    'recorded_by' => $user->id,
                    'recorded_at' => $at,
                ]);

                $this->audit->log(
                    AuditEventType::AttendanceRecorded,
                    actorId: $user->id,
                    targetType: 'Attendance',
                    targetId: $attendance->id,
                    after: [
                        'schedule_id' => $schedule->id,
                        'attendance_date' => $date,
                        'status' => $status->value,
                    ],
                );

                return $attendance;
            });
        } catch (UniqueConstraintViolationException) {
            // Dua pengiriman bersamaan: constraint unik di database yang menang.
            $this->reject($user, $schedule, $at, AttendanceRejection::Duplicate);
        }
    }

    /** Mengubah status presensi (FR-11). Alasan wajib, nilai sebelum dan sesudah dicatat. */
    public function changeStatus(User $actor, Attendance $attendance, AttendanceStatus $newStatus, string $reason): Attendance
    {
        $this->authorizeUpdate($actor, $attendance, 'change_status_forbidden');

        $reason = trim($reason);

        if (mb_strlen($reason) < 10) {
            throw ValidationException::withMessages(['reason' => 'Alasan wajib diisi, minimal 10 karakter.']);
        }

        return DB::transaction(function () use ($actor, $attendance, $newStatus, $reason) {
            $locked = Attendance::query()->lockForUpdate()->findOrFail($attendance->id);

            if ($locked->status === $newStatus) {
                throw ValidationException::withMessages(['status' => 'Status baru sama dengan status saat ini.']);
            }

            // "verified" dicatat agar aturan AN-05 (ubah setelah diverifikasi) dapat dievaluasi.
            $before = [
                'status' => $locked->status->value,
                'verified' => $locked->verified_at !== null,
            ];

            $locked->update(['status' => $newStatus]);

            $this->audit->log(
                AuditEventType::AttendanceStatusChanged,
                actorId: $actor->id,
                targetType: 'Attendance',
                targetId: $locked->id,
                before: $before,
                after: ['status' => $newStatus->value],
                reason: $reason,
            );

            return $locked;
        });
    }

    /** Guru piket atau administrator memverifikasi presensi. */
    public function verify(User $actor, Attendance $attendance): Attendance
    {
        $this->authorizeUpdate($actor, $attendance, 'verify_forbidden');

        if ($attendance->verified_at !== null) {
            return $attendance;
        }

        return DB::transaction(function () use ($actor, $attendance) {
            $attendance->update(['verified_by' => $actor->id, 'verified_at' => now()]);

            $this->audit->log(
                AuditEventType::AttendanceVerified,
                actorId: $actor->id,
                targetType: 'Attendance',
                targetId: $attendance->id,
            );

            return $attendance;
        });
    }

    /** Untuk UI: menentukan badge dan apakah tombol "Isi presensi" ditampilkan. */
    public function sessionState(Schedule $schedule, ?Attendance $attendance, ?CarbonInterface $at = null): SessionState
    {
        if ($attendance !== null) {
            return SessionState::Recorded;
        }

        $at ??= now();
        [$opens, , $end] = $this->window($schedule, $at);

        return match (true) {
            $at->lt($opens) => SessionState::NotOpen,
            $at->gt($end) => SessionState::Closed,
            default => SessionState::Open,
        };
    }

    /** @return array{0: Carbon, 1: Carbon, 2: Carbon} [buka, mulai, selesai] */
    private function window(Schedule $schedule, CarbonInterface $at): array
    {
        $date = $at->toDateString();
        $tz = $at->getTimezone();

        $start = Carbon::parse("{$date} {$schedule->start_time}", $tz);
        $end = Carbon::parse("{$date} {$schedule->end_time}", $tz);
        $opens = $start->copy()->subMinutes((int) config('attendance.open_before_minutes'));

        return [$opens, $start, $end];
    }

    private function authorizeUpdate(User $actor, Attendance $attendance, string $code): void
    {
        if (Gate::forUser($actor)->denies('update', $attendance)) {
            $this->audit->log(
                AuditEventType::AccessDenied,
                AuditResult::Denied,
                actorId: $actor->id,
                targetType: 'Attendance',
                targetId: $attendance->id,
                reason: $code,
            );

            throw new AuthorizationException('Anda tidak berwenang melakukan tindakan ini.');
        }
    }

    private function reject(User $user, Schedule $schedule, CarbonInterface $at, AttendanceRejection $reason): never
    {
        // Akses ke jadwal orang lain dicatat sebagai peristiwa keamanan (input AN-04),
        // penolakan lainnya sebagai penolakan presensi (input AN-02 dan AN-03).
        $this->audit->log(
            $reason === AttendanceRejection::NotYourSchedule
                ? AuditEventType::AccessDenied
                : AuditEventType::AttendanceRejected,
            AuditResult::Denied,
            actorId: $user->id,
            targetType: 'Schedule',
            targetId: $schedule->id,
            after: [
                'attendance_date' => $at->toDateString(),
                'attempted_at' => $at->format('H:i:s'),
            ],
            reason: $reason->value,
        );

        throw new AttendanceRejectedException($reason);
    }
}
