<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UatPrepareTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_uat_prepare_creates_six_sessions_with_expected_statuses(): void
    {
        Carbon::setTestNow('2026-11-02 10:00:00');   // Senin

        $this->artisan('uat:prepare')->assertSuccessful();

        $monitoring = app(MonitoringService::class);
        $summary = $monitoring->summary($monitoring->sessionsFor());

        $this->assertSame(6, Schedule::where('is_active', true)->count());
        $this->assertSame(1, $summary['hadir']);
        $this->assertSame(1, $summary['terlambat']);
        $this->assertSame(2, $summary['menunggu']);
        $this->assertSame(2, $summary['belum_terkonfirmasi']);
        $this->assertSame(0, $summary['tidak_hadir']);
        $this->assertSame(2, $summary['needs_action']);
    }
}
