<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditIntegrityChecker
{
    public function __construct(
        private readonly AuditChain $chain,
        private readonly AuditCheckpoint $checkpoint,
    ) {}

    /** @return array{ok:bool,checked:int,problems:list<string>} */
    public function verify(): array
    {
        $problems = [];
        $previous = null;
        $count = 0;
        $lastHash = null;

        AuditLog::query()->chunkById(500, function ($logs) use (&$problems, &$previous, &$count, &$lastHash) {
            foreach ($logs as $log) {
                $count++;

                if ($log->previous_hash !== $previous) {
                    $problems[] = "Entri #{$log->id}: tautan ke entri sebelumnya tidak sesuai.";
                }

                if (! hash_equals($this->chain->hashForModel($log), $log->entry_hash)) {
                    $problems[] = "Entri #{$log->id}: isi entri tidak sesuai dengan hash-nya.";
                }

                $previous = $log->entry_hash;
                $lastHash = $log->entry_hash;
            }
        });

        $cp = $this->checkpoint->read();

        if ($cp === null) {
            if ($count > 0) {
                $problems[] = 'Checkpoint tidak ditemukan; penghapusan entri di ujung rantai tidak dapat dideteksi.';
            }
        } else {
            if ((int) $cp['count'] !== $count) {
                $problems[] = "Jumlah entri ({$count}) berbeda dari checkpoint ({$cp['count']}).";
            }
            if ($lastHash !== $cp['last_hash']) {
                $problems[] = 'Hash entri terakhir berbeda dari checkpoint.';
            }
        }

        return ['ok' => $problems === [], 'checked' => $count, 'problems' => $problems];
    }
}
