<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerPinMapping extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'finger_pin_mappings';

    protected $fillable = [
        'school_id', 'pin', 'entity_type', 'entity_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Temukan mapping berdasarkan PIN.
     * Return ['entity_type' => ..., 'entity' => Student|Staff|null]
     */
    public static function resolvePin(string $schoolId, string $pin): ?array
    {
        $mapping = static::where('school_id', $schoolId)
            ->where('pin', $pin)
            ->where('is_active', true)
            ->first();

        if (!$mapping) return null;

        if ($mapping->entity_type === 'student') {
            $entity = Student::find($mapping->entity_id);
        } else {
            $entity = Staff::find($mapping->entity_id);
        }

        return [
            'type' => $mapping->entity_type,
            'entity' => $entity,
        ];
    }
}
