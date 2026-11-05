<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditIntegrityChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UatCheck extends Command
{
    protected $signature = 'uat:check';

    protected $description = 'Memeriksa kesiapan lingkungan sebelum UAT';

    public function handle(): int
    {
        $checkpointDir = dirname((string) config('audit.checkpoint_path'));

        // [label, pemeriksaan, wajib]
        $checks = [
            ['Zona waktu Asia/Jakarta', fn () => config('app.timezone') === 'Asia/Jakarta', true],
            ['Bahasa antarmuka id', fn () => config('app.locale') === 'id', true],
            ['APP_DEBUG nonaktif', fn () => ! config('app.debug'), false],
            ['Koneksi database', fn () => DB::select('select 1') !== [], true],
            ['Tiga peran tersedia', fn () => Role::count() === 3, true],
            ['Akun administrator ada', fn () => User::role('administrator')->exists(), true],
            ['Akun guru piket ada', fn () => User::role('guru_piket')->exists(), true],
            ['Akun guru ada', fn () => User::role('guru')->exists(), true],
            ['Folder checkpoint dapat ditulis', fn () => (is_dir($checkpointDir) || @mkdir($checkpointDir, 0755, true)) && is_writable($checkpointDir), true],
            ['Rantai audit log valid', fn () => app(AuditIntegrityChecker::class)->verify()['ok'], true],
        ];

        $failed = 0;

        foreach ($checks as [$label, $check, $required]) {
            try {
                $ok = (bool) $check();
            } catch (\Throwable) {
                $ok = false;
            }

            $this->line(($ok ? '  [OK]      ' : ($required ? '  [GAGAL]   ' : '  [PERIKSA] ')).$label);

            if (! $ok && $required) {
                $failed++;
            }
        }

        $this->newLine();
        $failed === 0
            ? $this->info('Lingkungan siap.')
            : $this->error("{$failed} pemeriksaan wajib gagal.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
