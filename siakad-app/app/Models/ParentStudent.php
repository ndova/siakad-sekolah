<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentStudent extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'parent_student';
    public $timestamps = false;

    protected $fillable = ['parent_id','student_id','is_primary'];
    protected function casts(): array { return ['is_primary'=>'boolean']; }
    public function parent(): BelongsTo { return $this->belongsTo(Guardian::class,'parent_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
