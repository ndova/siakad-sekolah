<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['school_id','invoice_number','student_id','academic_year_id','semester','batch_id','status','subtotal','discount','total','due_date','paid_at','voided_at','void_reason','notes','created_by'];
    protected function casts(): array { return ['due_date'=>'date','paid_at'=>'datetime','voided_at'=>'datetime']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function getPaidAmountAttribute(): float { return (float) $this->payments()->where('status','verified')->sum('amount'); }
    public function getRemainingAttribute(): float { return max(0, $this->total - $this->paid_amount); }
}
