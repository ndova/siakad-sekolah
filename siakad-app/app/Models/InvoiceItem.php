<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['invoice_id','fee_type_id','fee_name','description','quantity','unit_price','subtotal','period_month','period_year'];
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function feeType(): BelongsTo { return $this->belongsTo(FeeType::class); }
}
