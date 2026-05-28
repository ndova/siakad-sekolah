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
    protected function casts(): array { return ['media'=>'array']; }

    /** Get options always as array, handling object-format and double-encoded JSON in SQLite */
    protected function getOptionsAttribute(): array
    {
        $raw = $this->attributes['options'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        if (is_array($decoded)) return $decoded;
        return [];
    }

    protected function setOptionsAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['options'] = json_encode($value);
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['options'] = is_array($decoded) ? json_encode($decoded) : $value;
        } else {
            $this->attributes['options'] = json_encode([]);
        }
    }

    /** Get media always as array */
    protected function getMediaAttribute(): array
    {
        $raw = $this->attributes['media'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        return is_array($decoded) ? $decoded : [];
    }

    protected function setMediaAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['media'] = json_encode($value);
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['media'] = is_array($decoded) ? json_encode($decoded) : $value;
        } else {
            $this->attributes['media'] = json_encode([]);
        }
    }
    public function questionBank(): BelongsTo { return $this->belongsTo(QuestionBank::class); }
    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }
}
