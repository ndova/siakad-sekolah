<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamSession extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['exam_id','student_id','started_at','finished_at','remaining_seconds','status','ip_address','device_info'];
    protected function casts(): array { return ['started_at'=>'datetime','finished_at'=>'datetime']; }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function answers(): HasMany { return $this->hasMany(ExamAnswer::class); }
    public function result(): HasOne { return $this->hasOne(ExamResult::class); }
}
