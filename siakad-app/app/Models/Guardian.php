<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'parents';

    protected $fillable = ['user_id','nama_lengkap','jk','hubungan','pekerjaan','phone','alamat'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function students(): BelongsToMany { return $this->belongsToMany(Student::class,'parent_student','parent_id','student_id')->withPivot('is_primary'); }
    public function getChildInvoicesAttribute() { return Invoice::whereIn('student_id',$this->students()->pluck('students.id')); }
    public function getChildPaymentsAttribute() { return Payment::whereIn('student_id',$this->students()->pluck('students.id')); }
}
