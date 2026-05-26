<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $fillable = ['user_id','school_id','class_id','nisn','nis','kode_dapodik','nik','is_verified_nisn','nama_lengkap','jk','tempat_lahir','tanggal_lahir','agama','alamat','phone','nama_ayah','nama_ibu','status','tanggal_masuk'];
    protected function casts(): array { return ['tanggal_lahir'=>'date','tanggal_masuk'=>'date','is_verified_nisn'=>'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function class(): BelongsTo { return $this->belongsTo(SchoolClass::class,'class_id'); }
    public function parents(): BelongsToMany { return $this->belongsToMany(Guardian::class,'parent_student','student_id','parent_id')->withPivot('is_primary'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function p5Assessments(): HasMany { return $this->hasMany(P5Assessment::class); }
    public function examSessions(): HasMany { return $this->hasMany(ExamSession::class); }
    public function examResults(): HasMany { return $this->hasMany(ExamResult::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function classHistory(): HasMany { return $this->hasMany(StudentClassHistory::class); }
    public function pklRecords(): HasMany { return $this->hasMany(PklRecord::class); }
}
