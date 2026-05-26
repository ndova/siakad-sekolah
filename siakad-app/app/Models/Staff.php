<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_id', 'user_id', 'nama_lengkap', 'nip', 'nuptk',
        'jabatan', 'golongan', 'pendidikan_terakhir',
        'tempat_lahir', 'tanggal_lahir', 'jk', 'agama', 'alamat',
        'phone', 'photo', 'tanggal_masuk', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByJabatan($query, string $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }

    public function scopeBySchool($query, string $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    // ─── Helpers ───────────────────────────────────────────────

    /**
     * Map jabatan ke role user.
     */
    public static function mapJabatanToRole(string $jabatan): string
    {
        return match ($jabatan) {
            'kepsek' => 'kepsek',
            'guru' => 'guru',
            'walikelas' => 'walikelas',
            'bendahara' => 'bendahara',
            'bk' => 'bk',
            'tu' => 'tata_usaha',
            'pustakawan' => 'perpustakaan',
            'admin' => 'admin',
            default => 'guru',
        };
    }

    /** Daftar jabatan standar */
    public static function jabatanList(): array
    {
        return [
            'kepsek' => 'Kepala Sekolah',
            'guru' => 'Guru Mapel',
            'walikelas' => 'Wali Kelas',
            'bk' => 'Guru BK / Konselor',
            'bendahara' => 'Bendahara',
            'tu' => 'Tata Usaha',
            'pustakawan' => 'Pustakawan',
            'laboran' => 'Laboran',
            'satpam' => 'Satpam',
            'kebersihan' => 'Petugas Kebersihan',
            'staff' => 'Staf',
            'admin' => 'Admin Sekolah',
        ];
    }

    public static function jabatanLabel(string $jabatan): string
    {
        return self::jabatanList()[$jabatan] ?? ucfirst($jabatan);
    }

    /**
     * Rekap absensi seorang staff untuk periode tertentu.
     */
    public function recapAttendance(string $startDate, string $endDate): array
    {
        $records = $this->attendances()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $total = $records->count();
        $hadir = $records->where('status', 'hadir')->count();
        $terlambat = $records->where('status', 'terlambat')->count();

        return [
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $records->where('status', 'izin')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'alfa' => $records->where('status', 'alfa')->count(),
            'total' => $total,
            'persentase_hadir' => $total > 0 ? round((($hadir + $terlambat) / $total) * 100, 1) : 0,
            'persentase_hadir_murni' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
        ];
    }
}
