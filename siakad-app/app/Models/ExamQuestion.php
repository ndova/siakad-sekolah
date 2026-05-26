<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'exam_questions';

    public $timestamps = false;
    protected $fillable = ['exam_id','question_id','urutan','score_override'];
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function answers(): HasMany { return $this->hasMany(ExamAnswer::class); }
}
