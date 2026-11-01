<?php

namespace App\Services;

class AuditCheckpoint
{
    public function path(): string
    {
        return config('audit.checkpoint_path');
    }

    /** @return array{last_id:int,last_hash:string,count:int}|null */
    public function read(): ?array
    {
        if (! is_file($this->path())) {
            return null;
        }

        $data = json_decode((string) file_get_contents($this->path()), true);

        return is_array($data) ? $data : null;
    }

    public function write(int $lastId, string $lastHash, int $count): void
    {
        $dir = dirname($this->path());

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $existing = $this->read();

        // Jangan mundur bila dua penulisan saling susul.
        if ($existing !== null && (int) $existing['last_id'] > $lastId) {
            return;
        }

        file_put_contents(
            $this->path(),
            json_encode([
                'last_id' => $lastId,
                'last_hash' => $lastHash,
                'count' => $count,
                'updated_at' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT),
            LOCK_EX,
        );
    }
}
