<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['student_id','semester_id','class_subject_id','nilai_akhir','predikat','deskripsi_cp','is_locked','locked_by','locked_at'];
    protected function casts(): array { return ['is_locked'=>'boolean','locked_at'=>'datetime']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function locker(): BelongsTo { return $this->belongsTo(User::class,'locked_by'); }
}
