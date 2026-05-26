<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffService
{
    public function __construct(private readonly string $schoolId) {}

    // ─── Date Helpers ──────────────────────────────────────────

    /**
     * Generate array of date strings for a given month.
     * @return array{start: string, end: string, dates: string[], days: int}
     */
    public function monthRange(int $year, int $month): array
    {
        $pad = fn(int $v) => str_pad($v, 2, '0', STR_PAD_LEFT);
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return [
            'start' => "{$year}-{$pad($month)}-01",
            'end'   => "{$year}-{$pad($month)}-{$days}",
            'dates' => array_map(fn(int $d) => "{$year}-{$pad($month)}-{$pad($d)}", range(1, $days)),
            'days'  => $days,
        ];
    }

    // ─── Staff Data ────────────────────────────────────────────

    /** Get filtered staff query for index page. */
    public function staffQuery(?string $search, ?string $jabatan, ?string $statusAktif)
    {
        return Staff::with('user')->bySchool($this->schoolId)
            ->when($search, fn($q, $s) => $q->where(fn($q) => $q
                ->where('nama_lengkap', 'like', "%{$s}%")
                ->orWhere('nip', 'like', "%{$s}%")
            ))
            ->when($jabatan, fn($q, $j) => $q->byJabatan($j))
            ->when($statusAktif !== null, fn($q) => $q->where('is_active', $statusAktif))
            ->orderBy('nama_lengkap');
    }

    /** Users without staff profile (for linking). */
    public function usersWithoutStaff(): Collection
    {
        return \App\Models\User::where('school_id', $this->schoolId)
            ->whereNotIn('role', ['siswa', 'orang_tua'])
            ->whereDoesntHave('staff')
            ->orderBy('name')
            ->get();
    }

    /** Count active staff by jabatan. */
    public function totalByJabatan(): array
    {
        return Staff::bySchool($this->schoolId)->active()
            ->selectRaw('jabatan, COUNT(*) as count')
            ->groupBy('jabatan')
            ->pluck('count', 'jabatan')
            ->toArray();
    }

    // ─── Attendance Grid ────────────────────────────────────────

    /** Build attendance grid: get staff list + attendance map. */
    public function attendanceGrid(int $year, int $month, ?string $jabatan): array
    {
        ['start' => $start, 'end' => $end, 'dates' => $dates, 'days' => $days] = $this->monthRange($year, $month);

        $staffQuery = Staff::with('user')->bySchool($this->schoolId)->active()
            ->orderBy('jabatan')->orderBy('nama_lengkap');

        if ($jabatan) {
            $staffQuery->byJabatan($jabatan);
        }

        $staffList = $staffQuery->paginate(20);

        $attendances = StaffAttendance::bySchool($this->schoolId)
            ->whereBetween('tanggal', [$start, $end])
            ->get()
            ->keyBy(fn($a) => $a->staff_id . '|' . $a->tanggal->format('Y-m-d'));

        $statusSummary = StaffAttendance::statusSummary($this->schoolId, $start, $end);

        return compact('staffList', 'dates', 'attendances', 'days', 'statusSummary', 'start', 'end');
    }

    // ─── Attendance CRUD ────────────────────────────────────────

    public function storeAttendance(array $data): void
    {
        StaffAttendance::updateOrCreate(
            ['staff_id' => $data['staff_id'], 'tanggal' => $data['tanggal']],
            [
                'id'             => Str::uuid(),
                'school_id'      => $this->schoolId,
                'status'         => $data['status'],
                'check_in_time'  => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'keterangan'     => $data['keterangan'] ?? null,
                'source'         => $data['source'] ?? 'manual',
                'created_by'     => auth()->id(),
            ]
        );
    }

    public function bulkStoreAttendance(string $tanggal, array $records): void
    {
        DB::transaction(function () use ($tanggal, $records) {
            foreach ($records as $record) {
                if (empty($record['status'])) continue;

                StaffAttendance::updateOrCreate(
                    ['staff_id' => $record['staff_id'], 'tanggal' => $tanggal],
                    [
                        'id'         => Str::uuid(),
                        'school_id'  => $this->schoolId,
                        'status'     => $record['status'],
                        'keterangan' => $record['keterangan'] ?? null,
                        'source'     => 'manual',
                        'created_by' => auth()->id(),
                    ]
                );
            }
        });
    }

    // ─── Recap ──────────────────────────────────────────────────

    public function attendanceRecap(int $year, int $month, ?string $jabatan): array
    {
        ['start' => $start, 'end' => $end, 'days' => $days] = $this->monthRange($year, $month);

        $byJabatan = StaffAttendance::recapByJabatan($this->schoolId, $start, $end);

        $staffQuery = Staff::with('user')->bySchool($this->schoolId)->active()
            ->orderBy('jabatan')->orderBy('nama_lengkap');

        if ($jabatan) {
            $staffQuery->byJabatan($jabatan);
        }

        $staffList = $staffQuery->paginate(20);
        $staffList->getCollection()->transform(function (Staff $staff) use ($start, $end) {
            $recap = $staff->recapAttendance($start, $end);
            $recap['staff'] = $staff;
            return $recap;
        });

        return compact('byJabatan', 'staffList', 'start', 'end');
    }

    // ─── Import ──────────────────────────────────────────────────

    /**
     * Import attendance from CSV (fingerprint machine).
     * @return array{imported: int, skipped: int}
     */
    public function importFromCSV(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        fgetcsv($handle); // skip header

        $imported = $skipped = 0;

        DB::transaction(function () use ($handle, &$imported, &$skipped) {
            while (($row = fgetcsv($handle)) !== false) {
                [$deviceSn, $nip, $tanggal, $checkIn, $checkOut] = array_pad($row, 5, null);

                if (!$nip || !$tanggal) { $skipped++; continue; }

                $staff = Staff::where('nip', $nip)->bySchool($this->schoolId)->first();
                if (!$staff) { $skipped++; continue; }

                $status = $this->determineStatus($checkIn);

                StaffAttendance::updateOrCreate(
                    ['staff_id' => $staff->id, 'tanggal' => $tanggal],
                    [
                        'id'             => Str::uuid(),
                        'school_id'      => $this->schoolId,
                        'status'         => $status,
                        'check_in_time'  => $checkIn ?: null,
                        'check_out_time' => $checkOut ?: null,
                        'source'         => 'mesin_absen',
                        'device_sn'      => $deviceSn,
                        'created_by'     => auth()->id(),
                    ]
                );

                $imported++;
            }
        });

        fclose($handle);

        return compact('imported', 'skipped');
    }

    // ─── Export ──────────────────────────────────────────────────

    /** Build CSV rows for attendance export. */
    public function exportRows(int $year, int $month): Collection
    {
        ['start' => $start, 'end' => $end] = $this->monthRange($year, $month);

        return Staff::with('user')->bySchool($this->schoolId)->active()
            ->orderBy('jabatan')->orderBy('nama_lengkap')
            ->get()
            ->map(function (Staff $staff) use ($start, $end) {
                $recap = $staff->recapAttendance($start, $end);
                $recap['nama'] = $staff->nama_lengkap;
                $recap['nip'] = $staff->nip;
                $recap['jabatan'] = Staff::jabatanLabel($staff->jabatan);
                return $recap;
            });
    }

    // ─── Private Helpers ────────────────────────────────────────

    private function determineStatus(?string $checkIn): string
    {
        if (!$checkIn) return 'alfa';
        return $checkIn > '07:30:00' ? 'terlambat' : 'hadir';
    }
}
