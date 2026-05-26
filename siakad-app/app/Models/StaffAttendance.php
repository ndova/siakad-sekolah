<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'staff_id', 'school_id', 'tanggal',
        'check_in_time', 'check_out_time',
        'status', 'keterangan',
        'source', 'device_sn', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeBySchool($query, string $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    public function scopeByMonth($query, int $month, int $year)
    {
        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }

    // ─── Static Helpers (mirror Attendance model) ─────────────

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
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
            'terlambat' => 'yellow',
            default => 'slate',
        };
    }

    /**
     * Summary status untuk dashboard: hadir, izin, sakit, alfa, terlambat count.
     */
    public static function statusSummary(string $schoolId, string $startDate, string $endDate): array
    {
        $counts = self::where('school_id', $schoolId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($counts);
        $hadir = ($counts['hadir'] ?? 0) + ($counts['terlambat'] ?? 0);

        return [
            'hadir' => $counts['hadir'] ?? 0,
            'terlambat' => $counts['terlambat'] ?? 0,
            'izin' => $counts['izin'] ?? 0,
            'sakit' => $counts['sakit'] ?? 0,
            'alfa' => $counts['alfa'] ?? 0,
            'total' => $total,
            'persentase_hadir' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
        ];
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'manual' => 'Manual (Operator)',
            'self_service' => 'Isi Sendiri',
            'mesin_absen' => 'Mesin Fingerprint',
            default => $source,
        };
    }

    /**
     * Rekap absensi per jabatan untuk dashboard kepsek.
     * Returns collection grouped by jabatan with summary stats.
     */
    public static function recapByJabatan(string $schoolId, string $startDate, string $endDate): array
    {
        $staffIds = Staff::where('school_id', $schoolId)
            ->where('is_active', true)
            ->pluck('id');

        $records = self::whereIn('staff_id', $staffIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $staffByJabatan = Staff::whereIn('id', $staffIds)
            ->get()
            ->groupBy('jabatan');

        $result = [];
        foreach ($staffByJabatan as $jabatan => $staffGroup) {
            $staffIdList = $staffGroup->pluck('id');
            $groupRecords = $records->whereIn('staff_id', $staffIdList);

            $total = $groupRecords->count();
            $hadir = $groupRecords->where('status', 'hadir')->count();
            $terlambat = $groupRecords->where('status', 'terlambat')->count();

            $result[] = [
                'jabatan' => $jabatan,
                'label' => Staff::jabatanLabel($jabatan),
                'total_staff' => $staffGroup->count(),
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $groupRecords->where('status', 'izin')->count(),
                'sakit' => $groupRecords->where('status', 'sakit')->count(),
                'alfa' => $groupRecords->where('status', 'alfa')->count(),
                'total' => $total,
                'persentase_kehadiran' => $total > 0 ? round((($hadir + $terlambat) / $total) * 100, 1) : 0,
            ];
        }

        return $result;
    }
}
