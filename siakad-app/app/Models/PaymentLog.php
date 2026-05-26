<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['payment_id','actor_id','action','old_status','new_status','notes'];
    public $timestamps = false;
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class,'actor_id'); }
}
