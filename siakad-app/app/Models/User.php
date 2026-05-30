<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens;

    protected $fillable = [
        'school_id', 'name', 'email', 'password', 'role',
        'nip', 'phone', 'photo', 'is_active', 'last_login_at', 'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Jika user ini adalah siswa
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    // Jika user ini adalah orang tua
    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    // Jika user ini adalah pegawai (staff)
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    // Jika user ini guru/walikelas → mapel yang diampu
    public function classSubjectsAsTeacher(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'teacher_id');
    }

    // Jika user ini walikelas → rombel yang diwalikan
    public function homeroomClass(): HasOne
    {
        return $this->hasOne(SchoolClass::class, 'wali_kelas_id');
    }

    // Karya user (created_by / graded_by / verified_by)
    public function createdGrades(): HasMany
    {
        return $this->hasMany(Grade::class, 'created_by');
    }

    public function createdQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function createdExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'created_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Audit / created_by
    public function createdQuestionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'created_by');
    }

    public function createdP5Projects(): HasMany
    {
        return $this->hasMany(P5Project::class, 'created_by');
    }

    public function createdP5Assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'created_by');
    }

    public function createdAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'created_by');
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'user_id');
    }

    // ─── Role Helpers ─────────────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function isTeacher(): bool
    {
        return in_array($this->role, ['guru', 'walikelas']);
    }

    public function isWaliKelas(): bool
    {
        return $this->role === 'walikelas';
    }

    public function isBendahara(): bool
    {
        return $this->role === 'bendahara';
    }

    public function isKepsek(): bool
    {
        return $this->role === 'kepsek';
    }

    public function isStudent(): bool
    {
        return $this->role === 'siswa';
    }

    public function isGuardian(): bool
    {
        return $this->role === 'orang_tua';
    }
}
