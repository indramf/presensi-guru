<?php

namespace Tests\Feature;

use App\Enums\AlertSeverity;
use App\Enums\AlertStatus;
use App\Enums\AnomalyRule;
use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SecurityAlert;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnomalyRulesTest extends TestCase
{
    use RefreshDatabase;

    protected string $checkpointPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkpointPath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'audit-cp-' . uniqid() . '.json';

        config(['audit.checkpoint_path' => $this->checkpointPath]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        if (file_exists($this->checkpointPath)) {
            unlink($this->checkpointPath);
        }

        parent::tearDown();
    }

    private function log(AuditEventType $event, array $args = []): AuditLog
    {
        return app(AuditLogger::class)->log($event, ...$args);
    }

    private function failedLogin(string $login): AuditLog
    {
        return $this->log(AuditEventType::LoginFailed, [
            'result' => AuditResult::Failed,
            'after' => ['login' => $login],
        ]);
    }

    private function alerts(AnomalyRule $rule)
    {
        return SecurityAlert::where('rule_code', $rule->value)->get();
    }

    public function test_an01_five_failed_logins_in_window_create_one_alert(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->failedLogin('a@presensi.test');
        }

        $this->assertCount(0, $this->alerts(AnomalyRule::An01));

        $this->failedLogin('a@presensi.test');

        $alerts = $this->alerts(AnomalyRule::An01);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Sedang, $alerts->first()->severity);
        $this->assertSame(AlertStatus::Baru, $alerts->first()->status);
        $this->assertNotNull($alerts->first()->related_audit_log_id);
    }

    public function test_an01_failures_on_different_accounts_do_not_add_up(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->failedLogin('a@presensi.test');
            $this->failedLogin('b@presensi.test');
        }

        $this->assertCount(0, $this->alerts(AnomalyRule::An01));
    }

    public function test_an01_failures_outside_window_do_not_count(): void
    {
        \Carbon\Carbon::setTestNow(now()->subMinutes(10));

        for ($i = 1; $i <= 3; $i++) {
            $this->failedLogin('a@presensi.test');
        }

        \Carbon\Carbon::setTestNow(now()->addMinutes(10));

        for ($i = 1; $i <= 2; $i++) {
            $this->failedLogin('a@presensi.test');
        }

        $this->assertCount(0, $this->alerts(AnomalyRule::An01));
    }

    public function test_an02_outside_window_rejection_creates_alert(): void
    {
        $entry = $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied,
            'targetType' => 'Schedule',
            'targetId' => 1,
            'after' => ['attendance_date' => '2026-11-04'],
            'reason' => 'outside_window',
        ]);

        $alerts = $this->alerts(AnomalyRule::An02);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Rendah, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an02_other_rejection_reason_does_not_create_alert(): void
    {
        $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied,
            'targetType' => 'Schedule',
            'targetId' => 1,
            'reason' => 'duplicate',
        ]);

        $this->assertCount(0, $this->alerts(AnomalyRule::An02));
    }

    public function test_an03_duplicate_attendance_rejection_creates_alert(): void
    {
        $entry = $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied,
            'targetType' => 'Schedule',
            'targetId' => 1,
            'after' => ['attendance_date' => '2026-11-04'],
            'reason' => 'duplicate',
        ]);

        $alerts = $this->alerts(AnomalyRule::An03);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Rendah, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an03_other_rejection_reason_does_not_create_alert(): void
    {
        $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied,
            'targetType' => 'Schedule',
            'targetId' => 1,
            'reason' => 'outside_window',
        ]);

        $this->assertCount(0, $this->alerts(AnomalyRule::An03));
    }

    public function test_an04_single_access_denied_creates_medium_alert(): void
    {
        $user = \App\Models\User::factory()->create();

        $entry = $this->log(AuditEventType::AccessDenied, [
            'result' => AuditResult::Denied,
            'actorId' => $user->id,
            'targetType' => 'Route',
            'reason' => 'http_403',
        ]);

        $alerts = $this->alerts(AnomalyRule::An04);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Sedang, $alerts->first()->severity);
        $this->assertSame($user->id, $alerts->first()->related_user_id);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an04_third_access_denied_raises_high_alert(): void
    {
        $user = \App\Models\User::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            $this->log(AuditEventType::AccessDenied, [
                'result' => AuditResult::Denied,
                'actorId' => $user->id,
                'targetType' => 'Route',
                'reason' => 'http_403',
            ]);
        }

        $alerts = $this->alerts(AnomalyRule::An04);

        $this->assertNotEmpty($alerts);
        $this->assertSame(AlertSeverity::Tinggi, $alerts->last()->severity);
    }

    public function test_an04_access_denied_without_actor_does_not_create_alert(): void
    {
        $this->log(AuditEventType::AccessDenied, [
            'result' => AuditResult::Denied,
            'targetType' => 'Route',
            'reason' => 'http_403',
        ]);

        $this->assertCount(0, $this->alerts(AnomalyRule::An04));
    }

    public function test_an05_verified_attendance_status_change_creates_alert(): void
    {
        $entry = $this->log(AuditEventType::AttendanceStatusChanged, [
            'result' => AuditResult::Success,
            'targetType' => 'Attendance',
            'targetId' => 10,
            'before' => [
                'verified' => true,
                'status' => 'hadir',
            ],
            'after' => [
                'verified' => true,
                'status' => 'izin',
            ],
        ]);

        $alerts = $this->alerts(AnomalyRule::An05);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Sedang, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an05_unverified_attendance_status_change_does_not_create_alert(): void
    {
        $this->log(AuditEventType::AttendanceStatusChanged, [
            'result' => AuditResult::Success,
            'targetType' => 'Attendance',
            'targetId' => 10,
            'before' => [
                'verified' => false,
                'status' => 'hadir',
            ],
            'after' => [
                'verified' => false,
                'status' => 'izin',
            ],
        ]);

        $this->assertCount(0, $this->alerts(AnomalyRule::An05));
    }

    public function test_an06a_schedule_change_on_effective_day_creates_alert(): void
    {
        Carbon::setTestNow('2026-11-02 06:00:00');

        $schedule = Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);

        $entry = $this->log(AuditEventType::DataUpdated, [
            'result' => AuditResult::Success,
            'targetType' => 'Schedule',
            'targetId' => $schedule->id,
            'before' => [
                'day_of_week' => 1,
                'start_time' => '07:00:00',
                'end_time' => '07:45:00',
            ],
            'after' => [
                'start_time' => '07:15:00',
            ],
        ]);

        $alerts = $this->alerts(AnomalyRule::An06);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Rendah, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an06a_schedule_change_outside_two_hour_window_does_not_create_alert(): void
    {
        Carbon::setTestNow('2026-11-02 04:00:00');

        $schedule = Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);

        $this->log(AuditEventType::DataUpdated, [
            'result' => AuditResult::Success,
            'targetType' => 'Schedule',
            'targetId' => $schedule->id,
            'before' => [
                'day_of_week' => 1,
                'start_time' => '07:00:00',
            ],
            'after' => [
                'start_time' => '07:15:00',
            ],
        ]);

        $this->assertCount(0, $this->alerts(AnomalyRule::An06));
    }

    public function test_an06b_inactive_schedule_rejection_creates_alert(): void
    {
        $entry = $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied,
            'targetType' => 'Schedule',
            'targetId' => 1,
            'reason' => 'inactive_schedule',
        ]);

        $alerts = $this->alerts(AnomalyRule::An06);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Rendah, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an06b_wrong_day_rejection_creates_alert(): void
    {
        $entry = $this->log(AuditEventType::AttendanceRejected, [
            'result' => AuditResult::Denied,
            'targetType' => 'Schedule',
            'targetId' => 1,
            'reason' => 'wrong_day',
        ]);

        $alerts = $this->alerts(AnomalyRule::An06);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Rendah, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an07_twenty_admin_activities_create_medium_alert(): void
    {
        $user = \App\Models\User::factory()->create();

        for ($i = 1; $i <= 20; $i++) {
            $entry = $this->log(AuditEventType::DataCreated, [
                'result' => AuditResult::Success,
                'actorId' => $user->id,
                'targetType' => 'Teacher',
                'targetId' => $i,
            ]);
        }

        $alerts = $this->alerts(AnomalyRule::An07);

        $this->assertNotEmpty($alerts);
        $this->assertSame(AlertSeverity::Sedang, $alerts->last()->severity);
        $this->assertSame($entry->id, $alerts->last()->related_audit_log_id);
    }

    public function test_an07_admin_activity_outside_office_hours_creates_low_alert(): void
    {
        Carbon::setTestNow('2026-11-02 19:00:00');

        $user = \App\Models\User::factory()->create();

        $entry = $this->log(AuditEventType::DataCreated, [
            'result' => AuditResult::Success,
            'actorId' => $user->id,
            'targetType' => 'Teacher',
            'targetId' => 1,
        ]);

        $alerts = $this->alerts(AnomalyRule::An07);

        $this->assertCount(1, $alerts);
        $this->assertSame(AlertSeverity::Rendah, $alerts->first()->severity);
        $this->assertSame($entry->id, $alerts->first()->related_audit_log_id);
    }

    public function test_an07_admin_activity_during_office_hours_does_not_create_alert(): void
    {
        Carbon::setTestNow('2026-11-02 10:00:00');

        $user = \App\Models\User::factory()->create();

        $this->log(AuditEventType::DataCreated, [
            'result' => AuditResult::Success,
            'actorId' => $user->id,
            'targetType' => 'Teacher',
            'targetId' => 1,
        ]);

        $this->assertCount(0, $this->alerts(AnomalyRule::An07));
    }
}
