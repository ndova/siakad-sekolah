<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamResult extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['exam_session_id','student_id','exam_id','total_score','correct_count','wrong_count','tp_scores','is_passed','graded_by','graded_at'];
    protected function casts(): array { return ['tp_scores'=>'array','is_passed'=>'boolean','graded_at'=>'datetime']; }
    public function examSession(): BelongsTo { return $this->belongsTo(ExamSession::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function grader(): BelongsTo { return $this->belongsTo(User::class,'graded_by'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
}
