<?php

namespace App\Services;

use App\Models\AuditLog;
use Carbon\CarbonInterface;

class AuditChain
{
    public const TIME_FORMAT = 'Y-m-d H:i:s.u';

    /** Hash dari data mentah (dipakai saat menulis). */
    public function hash(array $data): string
    {
        $json = json_encode(
            $this->canonical($this->payload($data)),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        $key = config('audit.hmac_key');

        return $key ? hash_hmac('sha256', $json, $key) : hash('sha256', $json);
    }

    /** Hash yang dihitung ulang dari baris di database (dipakai saat verifikasi). */
    public function hashForModel(AuditLog $log): string
    {
        return $this->hash([
            'actor_user_id' => $log->actor_user_id,
            'event_type' => $log->event_type->value,
            'target_type' => $log->target_type,
            'target_id' => $log->target_id,
            'action_result' => $log->action_result->value,
            'before_data' => $log->before_data,
            'after_data' => $log->after_data,
            'reason' => $log->reason,
            'occurred_at' => $log->occurred_at,
            'previous_hash' => $log->previous_hash,
        ]);
    }

    /** Buang kunci sensitif secara rekursif. */
    public function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitive = array_map('strtolower', config('audit.sensitive_keys', []));
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $sensitive, true)) {
                continue;
            }
            $clean[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $clean;
    }

    private function payload(array $d): array
    {
        $occurred = $d['occurred_at'];

        return [
            'actor_user_id' => $d['actor_user_id'] === null ? null : (int) $d['actor_user_id'],
            'event_type' => $d['event_type'],
            'target_type' => $d['target_type'],
            'target_id' => $d['target_id'] === null ? null : (int) $d['target_id'],
            'action_result' => $d['action_result'],
            'before_data' => $d['before_data'],
            'after_data' => $d['after_data'],
            'reason' => $d['reason'],
            'occurred_at' => $occurred instanceof CarbonInterface
                ? $occurred->format(self::TIME_FORMAT)
                : (string) $occurred,
            'previous_hash' => $d['previous_hash'],
        ];
    }

    /** Urutkan key array asosiatif agar hash tidak bergantung urutan. */
    private function canonical(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(fn ($v) => $this->canonical($v), $value);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
