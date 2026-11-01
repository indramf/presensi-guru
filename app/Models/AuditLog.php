<?php

namespace App\Models;

use App\Enums\AuditEventType;
use App\Enums\AuditResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;   // tabelnya tanpa created_at/updated_at

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'actor_user_id',
        'event_type',
        'target_type',
        'target_id',
        'action_result',
        'before_data',
        'after_data',
        'reason',
        'occurred_at',
        'previous_hash',
        'entry_hash',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => AuditEventType::class,
            'action_result' => AuditResult::class,
            'before_data' => 'array',
            'after_data' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Audit log bersifat append-only.');
        });

        static::deleting(function () {
            throw new LogicException('Audit log bersifat append-only.');
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
