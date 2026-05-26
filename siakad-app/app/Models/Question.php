<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['question_bank_id','learning_objective_id','type','content','media','options','answer_key','score','level_kognitif','difficulty','created_by'];
    protected function casts(): array { return ['options'=>'array','media'=>'array']; }
    public function questionBank(): BelongsTo { return $this->belongsTo(QuestionBank::class); }
    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }
}
