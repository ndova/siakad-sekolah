<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningOutcome extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'learning_outcomes';

    protected $fillable = ['curriculum_id','subject_id','phase','code','description','urutan'];
    public function curriculum(): BelongsTo { return $this->belongsTo(Curriculum::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function learningObjectives(): HasMany { return $this->hasMany(LearningObjective::class); }
}
