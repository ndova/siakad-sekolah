<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademicYear extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','code','start_date','end_date','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean','start_date'=>'date','end_date'=>'date']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function semesters(): HasMany { return $this->hasMany(Semester::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function curriculum(): HasOne { return $this->hasOne(Curriculum::class); }
}
