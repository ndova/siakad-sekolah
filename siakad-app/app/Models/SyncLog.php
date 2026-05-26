<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'direction', 'entity_type', 'status',
        'total_records', 'success_count', 'error_count',
        'error_details', 'triggered_by', 'file_path',
        'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'error_details' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    const DIRECTION_IMPORT = 'import';
    const DIRECTION_EXPORT = 'export';

    public function markProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING, 'started_at' => now()]);
    }

    public function markSuccess(int $count, ?string $filePath = null): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'success_count' => $count,
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_count' => $this->total_records,
            'error_details' => ['message' => $message],
            'completed_at' => now(),
        ]);
    }

    public function progressPercent(): int
    {
        if ($this->total_records === 0) return 0;
        return (int) round(($this->success_count / $this->total_records) * 100);
    }
}
