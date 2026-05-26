<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','payment_number','invoice_id','student_id','paid_by','payment_method_id','payment_channel','amount','admin_fee','gateway_ref','gateway_status','proof_file','status','verified_by','verified_at','reject_reason','payment_date','paid_at','notes'];
    protected function casts(): array { return ['payment_date'=>'date','verified_at'=>'datetime','paid_at'=>'datetime']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class,'verified_by'); }
    public function logs(): HasMany { return $this->hasMany(PaymentLog::class); }
}
