<?php

namespace App\Services;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use App\Events\AuditEntryRecorded;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public function __construct(
        private readonly AuditChain $chain,
        private readonly AuditCheckpoint $checkpoint,
    ) {}

    public function log(
        AuditEventType $event,
        AuditResult $result = AuditResult::Success,
        ?int $actorId = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
    ): AuditLog {
        $count = 0;

        $entry = DB::transaction(function () use (
            $event,
            $result,
            $actorId,
            $targetType,
            $targetId,
            $before,
            $after,
            $reason,
            &$count,
        ) {
            // Kunci entri terakhir agar dua penulisan tidak membuat rantai bercabang.
            $last = AuditLog::query()->orderByDesc('id')->lockForUpdate()->first();

            $data = [
                'actor_user_id' => $actorId,
                'event_type' => $event->value,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'action_result' => $result->value,
                'before_data' => $this->chain->sanitize($before),
                'after_data' => $this->chain->sanitize($after),
                'reason' => $reason,
                'occurred_at' => now(),
                'previous_hash' => $last?->entry_hash,
            ];

            $entry = AuditLog::create($data + ['entry_hash' => $this->chain->hash($data)]);
            $count = AuditLog::count();

            return $entry;
        });

        // Tulis checkpoint hanya setelah transaksi (termasuk transaksi luar) benar-benar commit.
        DB::afterCommit(function () use ($entry, $count) {
            $this->checkpoint->write($entry->id, $entry->entry_hash, $count);

            // Deteksi anomali berjalan SETELAH commit, di luar transaksi bisnis.
            event(new AuditEntryRecorded($entry));
        });

        return $entry;
    }
}
