<?php

namespace App\Console\Commands;

use App\Services\AuditIntegrityChecker;
use Illuminate\Console\Command;

class AuditVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa integritas rantai hash audit log';

    /**
     * Execute the console command.
     */
    public function handle(AuditIntegrityChecker $checker): int
    {
        $result = $checker->verify();

        $this->info("Entri diperiksa: {$result['checked']}");

        if ($result['ok']) {
            $this->info('Rantai audit log VALID dan sesuai checkpoint.');

            return self::SUCCESS;
        }

        $this->error('Ditemukan ketidaksesuaian:');
        foreach ($result['problems'] as $problem) {
            $this->line(' - '.$problem);
        }

        return self::FAILURE;
    }
}
