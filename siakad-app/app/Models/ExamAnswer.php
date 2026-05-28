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
    protected function casts(): array { return ['is_correct'=>'boolean']; }

    protected function getSelectedOptionsAttribute(): array
    {
        $raw = $this->attributes['selected_options'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        return is_array($decoded) ? $decoded : [];
    }

    protected function setSelectedOptionsAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['selected_options'] = json_encode($value);
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['selected_options'] = is_array($decoded) ? json_encode($decoded) : $value;
        } else {
            $this->attributes['selected_options'] = json_encode([]);
        }
    }
    public function examSession(): BelongsTo { return $this->belongsTo(ExamSession::class); }
    public function examQuestion(): BelongsTo { return $this->belongsTo(ExamQuestion::class); }
}
