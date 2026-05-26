<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','code','title','type','subject_id','class_ids','semester_id','start_time','end_time','duration','total_questions','total_score','random_questions','random_answers','show_result','max_devices','status','created_by'];
    protected function casts(): array { return ['class_ids'=>'array','start_time'=>'datetime','end_time'=>'datetime','random_questions'=>'boolean','random_answers'=>'boolean','show_result'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }
    public function sessions(): HasMany { return $this->hasMany(ExamSession::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }
}
