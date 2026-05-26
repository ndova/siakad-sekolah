<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','code','name','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
}
