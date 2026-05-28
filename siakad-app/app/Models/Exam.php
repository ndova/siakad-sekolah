<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','code','title','type','subject_id','class_ids','semester_id','start_time','end_time','duration','total_questions','total_score','minimum_score','random_questions','random_answers','show_result','max_devices','status','created_by'];
    protected function casts(): array { return ['start_time'=>'datetime','end_time'=>'datetime','random_questions'=>'boolean','random_answers'=>'boolean','show_result'=>'boolean','minimum_score'=>'decimal:2']; }

    /** Get class_ids always as array, handling double-encoded JSON in SQLite */
    protected function getClassIdsAttribute(): array
    {
        $raw = $this->attributes['class_ids'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        // Decode up to 2 levels for double-encoded JSON
        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        return is_array($decoded) ? $decoded : [];
    }

    /** Set class_ids: always encode once to JSON string */
    protected function setClassIdsAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['class_ids'] = json_encode($value);
        } elseif (is_string($value)) {
            // Already JSON — re-encode to clean up any double-encoding
            $decoded = json_decode($value, true);
            $this->attributes['class_ids'] = is_array($decoded) ? json_encode($decoded) : $value;
        } else {
            $this->attributes['class_ids'] = json_encode([]);
        }
    }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }
    public function sessions(): HasMany { return $this->hasMany(ExamSession::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }

    /** Resolve class_ids JSONB array ke collection SchoolClass */
    public function classes(): Collection
    {
        $ids = $this->class_ids;
        if (empty($ids) || !is_array($ids)) return new Collection();
        return SchoolClass::whereIn('id', $ids)->get();
    }

    /** Comma-separated class codes untuk ditampilkan di daftar */
    public function getClassCodesAttribute(): string
    {
        $ids = $this->class_ids;
        if (empty($ids) || !is_array($ids)) return '-';
        return SchoolClass::whereIn('id', $ids)->pluck('code')->implode(', ');
    }
}
