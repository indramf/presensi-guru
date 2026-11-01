<?php

namespace App\Models;

use App\Enums\ClassReportStatus;
use App\Enums\FollowUpStatus;
use Database\Factories\ClassReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassReport extends Model
{
    /** @use HasFactory<ClassReportFactory> */
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'report_date',
        'reported_by',
        'report_status',
        'description',
        'follow_up_status',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'report_status' => ClassReportStatus::class,
            'follow_up_status' => FollowUpStatus::class,
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
