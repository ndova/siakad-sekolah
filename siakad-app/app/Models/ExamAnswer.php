<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['exam_session_id','exam_question_id','selected_options','text_answer','is_correct','score'];
    protected function casts(): array { return ['selected_options'=>'array','is_correct'=>'boolean']; }
    public function examSession(): BelongsTo { return $this->belongsTo(ExamSession::class); }
    public function examQuestion(): BelongsTo { return $this->belongsTo(ExamQuestion::class); }
}
