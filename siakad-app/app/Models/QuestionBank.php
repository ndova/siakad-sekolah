<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','name','subject_id','class_id','created_by','is_shared'];
    protected function casts(): array { return ['is_shared'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function questions(): HasMany { return $this->hasMany(Question::class); }
}
