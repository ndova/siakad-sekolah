<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PklAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'pkl_record_id', 'aspek', 'nilai', 'predikat', 'catatan', 'kode_dapodik',
    ];

    protected function casts(): array
    {
        return ['nilai' => 'decimal:2'];
    }

    public function pklRecord(): BelongsTo
    {
        return $this->belongsTo(PklRecord::class);
    }
}
