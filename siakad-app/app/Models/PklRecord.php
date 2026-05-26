<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PklRecord extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id', 'class_id', 'semester_id',
        'nama_dudi', 'alamat_dudi', 'pembimbing_dudi', 'pembimbing_sekolah',
        'tanggal_mulai', 'tanggal_selesai', 'total_jam', 'kode_dapodik',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'total_jam' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(PklAssessment::class);
    }

    // Rata-rata nilai PKL
    public function rataRata(): ?float
    {
        return $this->assessments()->avg('nilai');
    }

    // Predikat otomatis
    public function predikat(): ?string
    {
        $rata = $this->rataRata();
        if ($rata === null) return null;
        return match (true) {
            $rata >= 90 => 'A',
            $rata >= 80 => 'B',
            $rata >= 70 => 'C',
            default => 'D',
        };
    }
}
