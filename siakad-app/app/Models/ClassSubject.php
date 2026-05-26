<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSubject extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'class_subject';

    protected $fillable = ['class_id','subject_id','semester_id','teacher_id','kkm','jam_per_minggu'];
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class,'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function learningObjectiveSubjects(): HasMany { return $this->hasMany(LearningObjectiveSubject::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
}
