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
    protected function casts(): array { return ['class_ids'=>'array','tanggal_mulai'=>'date','tanggal_selesai'=>'date']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function assessments(): HasMany { return $this->hasMany(P5Assessment::class); }
}
