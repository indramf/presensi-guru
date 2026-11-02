<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use App\Enums\MonitoringStatus;
use App\Models\Attendance;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['monitoring.unconfirmed_after_minutes' => 5]);
    }

    /** 2 November 2026 adalah hari Senin. */
    private function at(string $time, string $date = '2026-11-02'): Carbon
    {
        return Carbon::parse("{$date} {$time}");
    }

    private function mondaySchedule(): Schedule
    {
        return Schedule::factory()->create([
            'day_of_week' => 1,
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
        ]);
    }

    private function rows(string $time)
    {
        return app(MonitoringService::class)->sessionsFor($this->at('00:00'), $this->at($time));
    }

    public function test_fr09_session_without_record_is_belum_terkonfirmasi_not_tidak_hadir(): void
    {
        $this->mondaySchedule();

        $row = $this->rows('07:30:00')->first();

        $this->assertSame(MonitoringStatus::BelumTerkonfirmasi, $row->status);
        $this->assertNull($row->attendance);
    }

    public function test_fr09_session_is_menunggu_until_threshold(): void
    {
        $this->mondaySchedule();

        $this->assertSame(MonitoringStatus::Menunggu, $this->rows('06:50:00')->first()->status);
        $this->assertSame(MonitoringStatus::Menunggu, $this->rows('07:04:59')->first()->status);
        $this->assertSame(MonitoringStatus::BelumTerkonfirmasi, $this->rows('07:05:00')->first()->status);
    }

    public function test_fr09_attendance_status_is_mapped_to_monitoring_status(): void
    {
        $map = [
            [AttendanceStatus::Hadir, MonitoringStatus::Hadir],
            [AttendanceStatus::Terlambat, MonitoringStatus::Terlambat],
            [AttendanceStatus::Izin, MonitoringStatus::Izin],
            [AttendanceStatus::TidakHadir, MonitoringStatus::TidakHadir],
        ];

        foreach ($map as [$attendanceStatus, $expected]) {
            Attendance::query()->delete();
            Schedule::query()->delete();

            $schedule = $this->mondaySchedule();
            Attendance::factory()->create([
                'schedule_id' => $schedule->id,
                'attendance_date' => '2026-11-02',
                'status' => $attendanceStatus,
            ]);

            $this->assertSame($expected, $this->rows('07:30:00')->first()->status);
        }
    }

    public function test_only_active_schedules_of_that_weekday_are_listed(): void
    {
        $this->mondaySchedule();
        Schedule::factory()->create(['day_of_week' => 2]);                      // Selasa
        Schedule::factory()->create(['day_of_week' => 1, 'is_active' => false]); // nonaktif

        $this->assertCount(1, $this->rows('07:30:00'));
    }

    public function test_latest_class_report_is_attached(): void
    {
        $schedule = $this->mondaySchedule();

        ClassReport::factory()->create([
            'schedule_id' => $schedule->id,
            'report_date' => '2026-11-02',
            'report_status' => ClassReportStatus::KelasKosong,
        ]);
        $latest = ClassReport::factory()->create([
            'schedule_id' => $schedule->id,
            'report_date' => '2026-11-02',
            'report_status' => ClassReportStatus::KelasDiberiTugas,
        ]);

        $this->assertSame($latest->id, $this->rows('07:30:00')->first()->report->id);
    }

    public function test_needs_action_rules(): void
    {
        $schedule = $this->mondaySchedule();

        // Belum terkonfirmasi tanpa laporan: butuh tindakan.
        $this->assertTrue($this->rows('07:30:00')->first()->needsAction);

        // Laporan "guru hadir": tidak perlu tindakan.
        $report = ClassReport::factory()->create([
            'schedule_id' => $schedule->id,
            'report_date' => '2026-11-02',
            'report_status' => ClassReportStatus::GuruHadir,
            'follow_up_status' => FollowUpStatus::Selesai,
        ]);
        $this->assertFalse($this->rows('07:30:00')->first()->needsAction);

        // Laporan kelas kosong, tindak lanjut belum: butuh tindakan.
        $report->update([
            'report_status' => ClassReportStatus::KelasKosong,
            'follow_up_status' => FollowUpStatus::Belum,
        ]);
        $this->assertTrue($this->rows('07:30:00')->first()->needsAction);

        // Tindak lanjut selesai: tidak perlu tindakan.
        $report->update(['follow_up_status' => FollowUpStatus::Selesai]);
        $this->assertFalse($this->rows('07:30:00')->first()->needsAction);
    }

    public function test_attended_session_without_report_does_not_need_action(): void
    {
        $schedule = $this->mondaySchedule();
        Attendance::factory()->create(['schedule_id' => $schedule->id, 'attendance_date' => '2026-11-02']);

        $this->assertFalse($this->rows('07:30:00')->first()->needsAction);
    }

    public function test_summary_counts_each_status(): void
    {
        $a = $this->mondaySchedule();
        Schedule::factory()->create(['day_of_week' => 1, 'start_time' => '07:45:00', 'end_time' => '08:30:00']);
        Attendance::factory()->create(['schedule_id' => $a->id, 'attendance_date' => '2026-11-02']);

        $summary = app(MonitoringService::class)->summary($this->rows('08:00:00'));

        $this->assertSame(1, $summary['hadir']);
        $this->assertSame(1, $summary['belum_terkonfirmasi']);
        $this->assertSame(1, $summary['needs_action']);
    }

    public function test_nfr01_query_count_does_not_grow_with_number_of_sessions(): void
    {
        Schedule::factory()->count(15)->create(['day_of_week' => 1]);

        DB::enableQueryLog();
        $this->rows('07:30:00');
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(8, $queries);
    }
}
