<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','code','name','kategori','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classSubjects(): HasMany { return $this->hasMany(ClassSubject::class); }
    public function learningOutcomes(): HasMany { return $this->hasMany(LearningOutcome::class); }
    public function questionBanks(): HasMany { return $this->hasMany(QuestionBank::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
}
