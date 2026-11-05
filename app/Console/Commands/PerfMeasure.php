<?php

namespace App\Console\Commands;

use App\Services\MonitoringService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PerfMeasure extends Command
{
    protected $signature = 'perf:measure {--runs=50} {--date= : Tanggal hari kerja (YYYY-MM-DD); default hari ini}';

    protected $description = 'Mengukur waktu penyusunan dashboard monitoring (sisi server)';

    public function handle(MonitoringService $monitoring): int
    {
        $runs = max(1, (int) $this->option('runs'));
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        $times = [];
        $queries = 0;
        $sessions = 0;

        for ($i = 0; $i < $runs; $i++) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $start = hrtime(true);
            $rows = $monitoring->sessionsFor($date);
            $monitoring->summary($rows);
            $times[] = (hrtime(true) - $start) / 1_000_000;

            $queries = count(DB::getQueryLog());
            $sessions = $rows->count();
        }

        sort($times);
        $p95 = $times[max(0, (int) ceil(0.95 * count($times)) - 1)];

        $this->table(
            ['Sesi', 'Query', 'Rata-rata (ms)', 'p95 (ms)', 'Maks (ms)'],
            [[$sessions, $queries, round(array_sum($times) / count($times), 1), round($p95, 1), round(max($times), 1)]],
        );

        if ($sessions === 0) {
            $this->warn('Tidak ada sesi pada tanggal itu. Gunakan --date pada hari kerja.');
        }

        return self::SUCCESS;
    }
}
