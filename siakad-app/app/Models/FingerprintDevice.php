<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FingerprintDevice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id', 'name', 'serial_number', 'ip_address', 'port',
        'model', 'location', 'is_active', 'last_sync_at', 'status', 'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_sync_at' => 'datetime',
            'config' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function fingerLogs(): HasMany
    {
        return $this->hasMany(FingerLog::class, 'device_id');
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
}
