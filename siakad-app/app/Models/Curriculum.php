<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','name','academic_year_id','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function learningOutcomes(): HasMany { return $this->hasMany(LearningOutcome::class); }
}
