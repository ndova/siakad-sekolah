<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonHour extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'class_id', 'subject_id', 'semester_id',
        'jam_per_minggu', 'total_jam_semester', 'kode_dapodik',
    ];

    protected function casts(): array
    {
        return ['jam_per_minggu' => 'integer', 'total_jam_semester' => 'integer'];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
