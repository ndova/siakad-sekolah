<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'classes';

    protected $fillable = ['school_id','academic_year_id','major_id','specialization_id','kurikulum_id','code','kode_dapodik','tingkat','jenjang','kapasitas','wali_kelas_id','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean','kapasitas'=>'integer']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function specialization(): BelongsTo { return $this->belongsTo(Specialization::class); }
    public function kurikulum(): BelongsTo { return $this->belongsTo(Curriculum::class); }
    public function waliKelas(): BelongsTo { return $this->belongsTo(User::class,'wali_kelas_id'); }
    public function classSubjects(): HasMany { return $this->hasMany(ClassSubject::class,'class_id'); }
    public function students(): HasMany { return $this->hasMany(Student::class,'class_id'); }
    public function lessonHours(): HasMany { return $this->hasMany(LessonHour::class,'class_id'); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class,'class_id'); }
}
