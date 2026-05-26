<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningObjectiveSubject extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'learning_objective_subjects';

    protected $fillable = ['learning_objective_id','class_subject_id','semester_id','urutan_ajar'];
    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
}
