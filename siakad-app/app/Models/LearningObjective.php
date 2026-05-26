<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningObjective extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'learning_objectives';

    protected $fillable = ['learning_outcome_id','code','description','level_kognitif','urutan'];
    public function learningOutcome(): BelongsTo { return $this->belongsTo(LearningOutcome::class); }
    public function learningObjectiveSubjects(): HasMany { return $this->hasMany(LearningObjectiveSubject::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function questions(): HasMany { return $this->hasMany(Question::class); }
}
