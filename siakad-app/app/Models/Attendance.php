<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['student_id','class_subject_id','semester_id','tanggal','status','keterangan','created_by'];
    protected function casts(): array { return ['tanggal'=>'date']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }

    /** Status labels with color codes */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
            'tidak_hadir' => 'Tidak Hadir',
            'terlambat' => 'Terlambat',
            default => 'Belum',
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'hadir' => 'green',
            'izin' => 'amber',
            'sakit' => 'orange',
            'alfa' => 'red',
            'tidak_hadir' => 'red',
            'terlambat' => 'yellow',
            default => 'slate',
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            'hadir' => '✅',
            'izin' => '📝',
            'sakit' => '🏥',
            'alfa' => '❌',
            'tidak_hadir' => '❌',
            'terlambat' => '⏰',
            default => '⬜',
        };
    }

    /**
     * Rekap absensi untuk satu siswa per semester (ringkasan + persentase)
     */
    public static function recapForStudent(string $studentId, string $semesterId): array
    {
        $records = self::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->get();

        $total = $records->count();
        $hadir = $records->where('status', 'hadir')->count();
        $terlambat = $records->where('status', 'terlambat')->count();

        return [
            'hadir' => $hadir,
            'izin' => $records->where('status', 'izin')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'alfa' => $records->where('status', 'alfa')->count() + $records->where('status', 'tidak_hadir')->count(),
            'terlambat' => $terlambat,
            'total' => $total,
            'persentase_hadir' => $total > 0 ? round((($hadir + $terlambat) / $total) * 100, 1) : 0,
            'persentase_hadir_murni' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
        ];
    }
}
