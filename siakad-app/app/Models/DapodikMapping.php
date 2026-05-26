<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DapodikMapping extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'entity_type', 'local_id', 'dapodik_id',
        'dapodik_code', 'metadata', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * Get Dapodik code for a given local entity.
     */
    public static function getCode(string $entityType, string $localId): ?string
    {
        return static::where('entity_type', $entityType)
            ->where('local_id', $localId)
            ->value('dapodik_code');
    }

    /**
     * Get Dapodik ID for a given local entity.
     */
    public static function getId(string $entityType, string $localId): ?string
    {
        return static::where('entity_type', $entityType)
            ->where('local_id', $localId)
            ->value('dapodik_id');
    }

    /**
     * Set or update mapping.
     */
    public static function setMapping(string $entityType, string $localId, ?string $dapodikId, ?string $dapodikCode, ?array $metadata = null): self
    {
        return static::updateOrCreate(
            ['entity_type' => $entityType, 'local_id' => $localId],
            [
                'dapodik_id' => $dapodikId,
                'dapodik_code' => $dapodikCode,
                'metadata' => $metadata,
                'last_synced_at' => now(),
            ]
        );
    }
}
