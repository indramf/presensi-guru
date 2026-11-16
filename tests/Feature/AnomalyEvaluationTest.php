<?php

namespace Tests\Feature;

use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnomalyEvaluationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function log(AuditEventType $event, array $args = []): void
    {
        app(AuditLogger::class)->log($event, ...$args);
    }

    private function failedLogins(string $login, int $times): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->log(AuditEventType::LoginFailed, ['result' => AuditResult::Failed, 'after' => ['login' => $login]]);
        }
    }

    private function rejection(string $reason, int $scheduleId): void
    {
        $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied, 'actorId' => User::factory()->create()->id,
            'targetType' => 'Schedule', 'targetId' => $scheduleId,
            'after' => ['attendance_date' => '2026-11-02'], 'reason' => $reason,
        ]);
    }

    /** @return list<array{rule: AnomalyRule, kind: string, name: string, run: Closure}> */
    private function scenarios(): array
    {
        return [
            // AN-01
            ['rule' => AnomalyRule::An01, 'kind' => 'anomaly', 'name' => '5 gagal login akun sama',
                'run' => fn () => $this->failedLogins('a1@x.test', 5)],
            ['rule' => AnomalyRule::An01, 'kind' => 'anomaly', 'name' => '6 gagal login akun sama',
                'run' => fn () => $this->failedLogins('a2@x.test', 6)],
            ['rule' => AnomalyRule::An01, 'kind' => 'anomaly', 'name' => '7 gagal login akun sama',
                'run' => fn () => $this->failedLogins('a3@x.test', 7)],
            ['rule' => AnomalyRule::An01, 'kind' => 'anomaly', 'name' => '8 gagal login akun sama',
                'run' => fn () => $this->failedLogins('a4@x.test', 8)],
            ['rule' => AnomalyRule::An01, 'kind' => 'anomaly', 'name' => '10 gagal login akun sama',
                'run' => fn () => $this->failedLogins('a5@x.test', 10)],

            ['rule' => AnomalyRule::An01, 'kind' => 'normal', 'name' => '1 gagal login',
                'run' => fn () => $this->failedLogins('n1@x.test', 1)],
            ['rule' => AnomalyRule::An01, 'kind' => 'normal', 'name' => '2 gagal login',
                'run' => fn () => $this->failedLogins('n2@x.test', 2)],
            ['rule' => AnomalyRule::An01, 'kind' => 'normal', 'name' => '3 gagal login',
                'run' => fn () => $this->failedLogins('n3@x.test', 3)],
            ['rule' => AnomalyRule::An01, 'kind' => 'normal', 'name' => '4 gagal login',
                'run' => fn () => $this->failedLogins('n4@x.test', 4)],
            ['rule' => AnomalyRule::An01, 'kind' => 'normal', 'name' => '4 gagal login akun berbeda', 'run' => fn () => $this->failedLogins('n5@x.test', 4)],

            // AN-02
            ['rule' => AnomalyRule::An02, 'kind' => 'anomaly', 'name' => 'outside window 1',
                'run' => fn () => $this->rejection('outside_window', 201)],
            ['rule' => AnomalyRule::An02, 'kind' => 'anomaly', 'name' => 'outside window 2',
                'run' => fn () => $this->rejection('outside_window', 202)],
            ['rule' => AnomalyRule::An02, 'kind' => 'anomaly', 'name' => 'outside window 3',
                'run' => fn () => $this->rejection('outside_window', 203)],
            ['rule' => AnomalyRule::An02, 'kind' => 'anomaly', 'name' => 'outside window 4',
                'run' => fn () => $this->rejection('outside_window', 204)],
            ['rule' => AnomalyRule::An02, 'kind' => 'anomaly', 'name' => 'outside window 5',
                'run' => fn () => $this->rejection('outside_window', 205)],

            ['rule' => AnomalyRule::An02, 'kind' => 'normal', 'name' => 'presensi normal 1',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 301])],
            ['rule' => AnomalyRule::An02, 'kind' => 'normal', 'name' => 'presensi normal 2',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 302])],
            ['rule' => AnomalyRule::An02, 'kind' => 'normal', 'name' => 'presensi normal 3',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 303])],
            ['rule' => AnomalyRule::An02, 'kind' => 'normal', 'name' => 'presensi normal 4',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 304])],
            ['rule' => AnomalyRule::An02, 'kind' => 'normal', 'name' => 'presensi normal 5',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 305])],

            // AN-03
            ['rule' => AnomalyRule::An03, 'kind' => 'anomaly', 'name' => 'presensi ganda 1',
                'run' => fn () => $this->rejection('duplicate', 501)],
            ['rule' => AnomalyRule::An03, 'kind' => 'anomaly', 'name' => 'presensi ganda 2',
                'run' => fn () => $this->rejection('duplicate', 502)],
            ['rule' => AnomalyRule::An03, 'kind' => 'anomaly', 'name' => 'presensi ganda 3',
                'run' => fn () => $this->rejection('duplicate', 503)],
            ['rule' => AnomalyRule::An03, 'kind' => 'anomaly', 'name' => 'presensi ganda 4',
                'run' => fn () => $this->rejection('duplicate', 504)],
            ['rule' => AnomalyRule::An03, 'kind' => 'anomaly', 'name' => 'presensi ganda 5',
                'run' => fn () => $this->rejection('duplicate', 505)],

            ['rule' => AnomalyRule::An03, 'kind' => 'normal', 'name' => 'presensi normal 1',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 601])],
            ['rule' => AnomalyRule::An03, 'kind' => 'normal', 'name' => 'presensi normal 2',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 602])],
            ['rule' => AnomalyRule::An03, 'kind' => 'normal', 'name' => 'presensi normal 3',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 603])],
            ['rule' => AnomalyRule::An03, 'kind' => 'normal', 'name' => 'presensi normal 4',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 604])],
            ['rule' => AnomalyRule::An03, 'kind' => 'normal', 'name' => 'presensi normal 5',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 605])],

            // AN-04
            ['rule' => AnomalyRule::An04, 'kind' => 'anomaly', 'name' => 'akses tanpa hak 1',
                'run' => fn () => $this->log(AuditEventType::AccessDenied, [
                    'result' => AuditResult::Denied, 'actorId' => User::factory()->create()->id, 'reason' => 'x'])],
            ['rule' => AnomalyRule::An04, 'kind' => 'anomaly', 'name' => 'akses tanpa hak 2',
                'run' => fn () => $this->log(AuditEventType::AccessDenied, [
                    'result' => AuditResult::Denied, 'actorId' => User::factory()->create()->id, 'reason' => 'x'])],
            ['rule' => AnomalyRule::An04, 'kind' => 'anomaly', 'name' => 'akses tanpa hak 3',
                'run' => fn () => $this->log(AuditEventType::AccessDenied, [
                    'result' => AuditResult::Denied, 'actorId' => User::factory()->create()->id, 'reason' => 'x'])],
            ['rule' => AnomalyRule::An04, 'kind' => 'anomaly', 'name' => 'akses tanpa hak 4',
                'run' => fn () => $this->log(AuditEventType::AccessDenied, [
                    'result' => AuditResult::Denied, 'actorId' => User::factory()->create()->id, 'reason' => 'x'])],
            ['rule' => AnomalyRule::An04, 'kind' => 'anomaly', 'name' => 'akses tanpa hak 5',
                'run' => fn () => $this->log(AuditEventType::AccessDenied, [
                    'result' => AuditResult::Denied, 'actorId' => User::factory()->create()->id, 'reason' => 'x'])],

            ['rule' => AnomalyRule::An04, 'kind' => 'normal', 'name' => 'logout biasa 1',
                'run' => fn () => $this->log(AuditEventType::Logout, ['actorId' => User::factory()->create()->id])],
            ['rule' => AnomalyRule::An04, 'kind' => 'normal', 'name' => 'logout biasa 2',
                'run' => fn () => $this->log(AuditEventType::Logout, ['actorId' => User::factory()->create()->id])],
            ['rule' => AnomalyRule::An04, 'kind' => 'normal', 'name' => 'logout biasa 3',
                'run' => fn () => $this->log(AuditEventType::Logout, ['actorId' => User::factory()->create()->id])],
            ['rule' => AnomalyRule::An04, 'kind' => 'normal', 'name' => 'logout biasa 4',
                'run' => fn () => $this->log(AuditEventType::Logout, ['actorId' => User::factory()->create()->id])],
            ['rule' => AnomalyRule::An04, 'kind' => 'normal', 'name' => 'logout biasa 5',
                'run' => fn () => $this->log(AuditEventType::Logout, ['actorId' => User::factory()->create()->id])],

            // AN-05
            ['rule' => AnomalyRule::An05, 'kind' => 'anomaly', 'name' => 'ubah status setelah verifikasi 1',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 701,
                    'before' => ['status' => 'terlambat', 'verified' => true],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'anomaly', 'name' => 'ubah status setelah verifikasi 2',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 702,
                    'before' => ['status' => 'terlambat', 'verified' => true],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'anomaly', 'name' => 'ubah status setelah verifikasi 3',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 703,
                    'before' => ['status' => 'terlambat', 'verified' => true],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'anomaly', 'name' => 'ubah status setelah verifikasi 4',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 704,
                    'before' => ['status' => 'terlambat', 'verified' => true],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'anomaly', 'name' => 'ubah status setelah verifikasi 5',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 705,
                    'before' => ['status' => 'terlambat', 'verified' => true],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],

            ['rule' => AnomalyRule::An05, 'kind' => 'normal', 'name' => 'ubah status sebelum verifikasi 1',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 801,
                    'before' => ['status' => 'terlambat', 'verified' => false],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'normal', 'name' => 'ubah status sebelum verifikasi 2',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 802,
                    'before' => ['status' => 'terlambat', 'verified' => false],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'normal', 'name' => 'ubah status sebelum verifikasi 3',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 803,
                    'before' => ['status' => 'terlambat', 'verified' => false],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'normal', 'name' => 'ubah status sebelum verifikasi 4',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 804,
                    'before' => ['status' => 'terlambat', 'verified' => false],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],
            ['rule' => AnomalyRule::An05, 'kind' => 'normal', 'name' => 'ubah status sebelum verifikasi 5',
                'run' => fn () => $this->log(AuditEventType::AttendanceStatusChanged, [
                    'actorId' => User::factory()->create()->id, 'targetType' => 'Attendance', 'targetId' => 805,
                    'before' => ['status' => 'terlambat', 'verified' => false],
                    'after' => ['status' => 'hadir'], 'reason' => 'Koreksi'])],

            // AN-06

            // Kondisi 1: Perubahan jadwal mendadak
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'jadwal diubah 30 menit sebelum mulai 1',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 06:30:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'jadwal diubah 30 menit sebelum mulai 2',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 06:30:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'jadwal diubah 30 menit sebelum mulai 3',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 06:30:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'jadwal diubah 30 menit sebelum mulai 4',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 06:30:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'jadwal diubah 30 menit sebelum mulai 5',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 06:30:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],

            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'jadwal diubah jauh sebelumnya 1',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 03:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'jadwal diubah jauh sebelumnya 2',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 03:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'jadwal diubah jauh sebelumnya 3',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 03:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'jadwal diubah jauh sebelumnya 4',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 03:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'jadwal diubah jauh sebelumnya 5',
                'run' => function () {
                    $s = Schedule::factory()->create([
                        'day_of_week' => 1, 'start_time' => '07:00:00', 'end_time' => '07:45:00',
                    ]);
                    Carbon::setTestNow('2026-11-02 03:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Schedule',
                        'targetId' => $s->id,
                        'before' => ['end_time' => '07:45:00'],
                        'after' => ['end_time' => '08:00:00'],
                    ]);
                }],

            // Kondisi 2: Jadwal tidak aktif atau hari yang salah
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'presensi pada jadwal tidak aktif 1',
                'run' => fn () => $this->rejection('inactive_schedule', 901)],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'presensi pada jadwal tidak aktif 2',
                'run' => fn () => $this->rejection('inactive_schedule', 902)],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'presensi pada jadwal tidak aktif 3',
                'run' => fn () => $this->rejection('inactive_schedule', 903)],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'presensi pada hari yang salah 1',
                'run' => fn () => $this->rejection('wrong_day', 904)],
            ['rule' => AnomalyRule::An06, 'kind' => 'anomaly', 'name' => 'presensi pada hari yang salah 2',
                'run' => fn () => $this->rejection('wrong_day', 905)],

            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'presensi normal untuk kondisi jadwal 1',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 911])],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'presensi normal untuk kondisi jadwal 2',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 912])],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'presensi normal untuk kondisi jadwal 3',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 913])],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'presensi normal untuk kondisi jadwal 4',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 914])],
            ['rule' => AnomalyRule::An06, 'kind' => 'normal', 'name' => 'presensi normal untuk kondisi jadwal 5',
                'run' => fn () => $this->log(AuditEventType::AttendanceRecorded, ['targetType' => 'Attendance', 'targetId' => 915])],

            // AN-07

            // Kondisi 1: Burst activity (20 perubahan dalam 10 menit)
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => '20 perubahan beruntun 1',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 10:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 20; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => '20 perubahan beruntun 2',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 10:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 20; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => '20 perubahan beruntun 3',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 10:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 20; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => '20 perubahan beruntun 4',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 10:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 20; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => '20 perubahan beruntun 5',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 10:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 20; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],

            // Normal: perubahan di bawah threshold burst
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => '3 perubahan pada jam kerja 1',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 3; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => '3 perubahan pada jam kerja 2',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 3; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => '3 perubahan pada jam kerja 3',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 3; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => '3 perubahan pada jam kerja 4',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 3; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => '3 perubahan pada jam kerja 5',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $actor = User::factory()->create()->id;

                    for ($i = 1; $i <= 3; $i++) {
                        $this->log(AuditEventType::DataUpdated, [
                            'actorId' => $actor, 'targetType' => 'Teacher',
                            'targetId' => $i, 'before' => ['full_name' => 'A'],
                            'after' => ['full_name' => 'B'],
                        ]);
                    }
                }],

            // Kondisi 2: Aktivitas di luar jam kerja
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => 'aktivitas admin di luar jam kerja 1',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 20:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1001, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => 'aktivitas admin di luar jam kerja 2',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 20:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1002, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => 'aktivitas admin di luar jam kerja 3',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 20:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1003, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => 'aktivitas admin di luar jam kerja 4',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 20:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1004, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'anomaly', 'name' => 'aktivitas admin di luar jam kerja 5',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 20:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1005, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],

            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => 'aktivitas admin dalam jam kerja 1',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1101, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => 'aktivitas admin dalam jam kerja 2',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1102, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => 'aktivitas admin dalam jam kerja 3',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1103, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => 'aktivitas admin dalam jam kerja 4',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1104, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
            ['rule' => AnomalyRule::An07, 'kind' => 'normal', 'name' => 'aktivitas admin dalam jam kerja 5',
                'run' => function () {
                    Carbon::setTestNow('2026-11-02 11:00:00');
                    $this->log(AuditEventType::DataUpdated, [
                        'actorId' => User::factory()->create()->id, 'targetType' => 'Teacher',
                        'targetId' => 1105, 'before' => ['full_name' => 'A'],
                        'after' => ['full_name' => 'B'],
                    ]);
                }],
        ];
    }

    public function test_t12_detection_rate_and_false_positive_per_rule(): void
    {
        $stats = [];

        foreach ($this->scenarios() as $scenario) {
            $rule = $scenario['rule'];
            $before = SecurityAlert::where('rule_code', $rule->value)->count();

            ($scenario['run'])();
            Carbon::setTestNow();

            $fired = SecurityAlert::where('rule_code', $rule->value)->count() > $before;

            $stats[$rule->value][$scenario['kind']][] = ['name' => $scenario['name'], 'fired' => $fired];
        }

        $report = "# Hasil Evaluasi Deteksi Anomali (T-12)\n\n"
            ."Skenario bersifat sintetis dan dibuat oleh tim; hasil **tidak** mewakili data sekolah nyata.\n\n"
            ."| Aturan | Skenario anomali | Terdeteksi | Detection rate | Skenario normal | Salah tandai | False positive |\n"
            ."|---|---|---|---|---|---|---|\n";

        foreach ($stats as $code => $kinds) {
            $anomalies = $kinds['anomaly'] ?? [];
            $normals = $kinds['normal'] ?? [];
            $detected = count(array_filter($anomalies, fn ($s) => $s['fired']));
            $flagged = count(array_filter($normals, fn ($s) => $s['fired']));
            $dr = count($anomalies) ? $detected / count($anomalies) : 0;
            $fp = count($normals) ? $flagged / count($normals) : 0;

            $report .= sprintf("| %s | %d | %d | %.0f%% | %d | %d | %.0f%% |\n",
                $code, count($anomalies), $detected, $dr * 100, count($normals), $flagged, $fp * 100);

            $this->assertGreaterThanOrEqual(0.9, $dr, "Detection rate {$code} di bawah target KS-06.");
            $this->assertLessThanOrEqual(0.1, $fp, "False positive {$code} di atas target KS-06.");
        }

        file_put_contents(storage_path('app/anomaly-evaluation.md'), $report);
    }

    public function test_t10_audit_entries_are_complete_and_contain_no_secrets(): void
    {
        foreach ($this->scenarios() as $scenario) {
            ($scenario['run'])();
            Carbon::setTestNow();
        }

        $this->assertGreaterThan(0, AuditLog::count());

        foreach (AuditLog::all() as $log) {
            $this->assertNotNull($log->event_type);
            $this->assertNotNull($log->action_result);
            $this->assertNotNull($log->occurred_at);
            $this->assertSame(64, strlen($log->entry_hash));

            $payload = strtolower(json_encode([
                $log->before_data,
                $log->after_data,
                $log->reason,
            ]));

            foreach (['password', 'token', 'secret'] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $payload);
            }
        }
    }
}
