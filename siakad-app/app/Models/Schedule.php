<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'class_subject_id', 'semester_id', 'hari',
        'jam_ke', 'ruangan', 'kode_dapodik',
    ];

    protected function casts(): array
    {
        return ['hari' => 'integer', 'jam_ke' => 'integer'];
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public static function hariLabel(int $hari): string
    {
        return match ($hari) {
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
            default => '-',
        };
    }
}
