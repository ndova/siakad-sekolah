<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'device_id', 'pin', 'scan_time', 'verify_mode',
        'io_mode', 'work_code', 'is_processed',
        'attendance_id', 'attendance_type', 'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'scan_time' => 'datetime',
            'is_processed' => 'boolean',
            'raw_data' => 'array',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class, 'device_id');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('scan_time', $date);
    }
}
