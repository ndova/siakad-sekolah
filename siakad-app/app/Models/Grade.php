<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['student_id','class_subject_id','learning_objective_id','semester_id','jenis_nilai','nilai','deskripsi','sumber','exam_result_id','created_by'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function examResult(): BelongsTo { return $this->belongsTo(ExamResult::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
