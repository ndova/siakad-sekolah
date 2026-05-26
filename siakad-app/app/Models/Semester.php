<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['academic_year_id','semester_number','start_date','end_date','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean','start_date'=>'date','end_date'=>'date']; }

    public function getNameAttribute(): string
    {
        return 'Semester ' . ($this->semester_number == 1 ? 'Ganjil' : 'Genap');
    }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
    public function p5Projects(): HasMany { return $this->hasMany(P5Project::class); }
}
