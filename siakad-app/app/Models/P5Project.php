<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5Project extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','semester_id','tema','judul','deskripsi','class_ids','tanggal_mulai','tanggal_selesai','created_by'];
    protected function casts(): array { return ['tanggal_mulai'=>'date','tanggal_selesai'=>'date']; }

    /** Get class_ids always as array, handling double-encoded JSON in SQLite */
    protected function getClassIdsAttribute(): array
    {
        $raw = $this->attributes['class_ids'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
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
            $decoded = json_decode($value, true);
            $this->attributes['class_ids'] = is_array($decoded) ? json_encode($decoded) : $value;
        } else {
            $this->attributes['class_ids'] = json_encode([]);
        }
    }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function assessments(): HasMany { return $this->hasMany(P5Assessment::class); }
}
