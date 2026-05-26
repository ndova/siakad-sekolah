<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassHistory extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['student_id','class_id','academic_year_id','semester_id','mulai','selesai'];
    protected function casts(): array { return ['mulai'=>'date','selesai'=>'date']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function class(): BelongsTo { return $this->belongsTo(SchoolClass::class,'class_id'); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
}
