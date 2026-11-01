<?php

namespace App\Models;

// use Database\Factories\SecurityAlertFactory;
use App\Enums\AlertSeverity;
use App\Enums\AlertStatus;
use App\Enums\AnomalyRule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlert extends Model
{
    /** @use HasFactory<SecurityAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'rule_code',
        'severity',
        'related_user_id',
        'related_audit_log_id',
        'description',
        'detected_at',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'rule_code' => AnomalyRule::class,
            'severity' => AlertSeverity::class,
            'status' => AlertStatus::class,
            'detected_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class, 'related_audit_log_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
