<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeTypeTarget extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['fee_type_id','target_level','jenjang','tingkat','jurusan_id','nominal_override'];
    public function feeType(): BelongsTo { return $this->belongsTo(FeeType::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class,'jurusan_id'); }
}
