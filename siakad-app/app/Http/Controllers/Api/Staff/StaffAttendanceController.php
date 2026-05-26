<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * API Absensi Pegawai — untuk portal staf & aplikasi mobile.
 *
 * Endpoint untuk pegawai: melihat riwayat absensi pribadi, isi presensi sendiri.
 * Endpoint untuk kepsek/admin: rekap absensi pegawai.
 */
class StaffAttendanceController extends Controller
{
    // ─── PEGAWAI: RIWAYAT ABSENSI PRIBADI ──────────────────────

    /**
     * GET /api/v1/staff/attendance
     * Riwayat absensi pegawai yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $query = $staff->attendances()
            ->when($request->has('month'), fn($q) => $q->byMonth($month, $year))
            ->when($request->tanggal, fn($q, $t) => $q->byDate($t))
            ->orderBy('tanggal', 'desc');

        $attendances = $query->paginate($request->integer('per_page', 31));

        // Recap for current month
        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-{$daysInMonth}";

        $recap = $staff->recapAttendance($startDate, $endDate);

        // Calendar data untuk bulan ini
        $calendarData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            $att = $attendances->first(fn($a) => $a->tanggal->format('Y-m-d') === $date);
            $calendarData[] = [
                'tanggal' => $date,
                'status' => $att?->status ?? null,
                'check_in_time' => $att?->check_in_time,
                'check_out_time' => $att?->check_out_time,
                'label' => $att ? StaffAttendance::statusLabel($att->status) : null,
                'color' => $att ? StaffAttendance::statusColor($att->status) : 'slate',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'staff' => [
                    'id' => $staff->id,
                    'nama' => $staff->nama_lengkap,
                    'nip' => $staff->nip,
                    'jabatan' => $staff->jabatan,
                    'jabatan_label' => Staff::jabatanLabel($staff->jabatan),
                ],
                'recap' => $recap,
                'calendar' => $calendarData,
                'attendances' => $attendances,
            ],
        ]);
    }

    /**
     * GET /api/v1/staff/attendance/summary
     * Ringkasan absensi untuk dashboard staf.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = auth()->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);
        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-{$daysInMonth}";

        $recap = $staff->recapAttendance($startDate, $endDate);

        // Today's status
        $today = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'recap_bulan_ini' => $recap,
                'hari_ini' => $today ? [
                    'status' => $today->status,
                    'label' => StaffAttendance::statusLabel($today->status),
                    'check_in_time' => $today->check_in_time,
                    'check_out_time' => $today->check_out_time,
                ] : null,
            ],
        ]);
    }

    // ─── PEGAWAI: ISI PRESENSI SENDIRI ─────────────────────────

    /**
     * POST /api/v1/staff/attendance/self
     * Pegawai mengisi absensi sendiri (self-service).
     */
    public function selfStore(Request $request): JsonResponse
    {
        $user = auth()->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,terlambat',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tanggal = now()->format('Y-m-d');

        // Prevent duplicate for today
        $existing = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi hari ini sudah tercatat.',
            ], 422);
        }

        $att = StaffAttendance::create([
            'id' => Str::uuid(),
            'staff_id' => $staff->id,
            'school_id' => $staff->school_id,
            'tanggal' => $tanggal,
            'status' => $data['status'],
            'keterangan' => $data['keterangan'] ?? null,
            'check_in_time' => $data['status'] === 'terlambat' ? now()->format('H:i:s') : now()->format('H:i:s'),
            'source' => 'self_service',
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat.',
            'data' => $att,
        ], 201);
    }

    /**
     * PUT /api/v1/staff/attendance/self
     * Koreksi absensi hari ini (sebelum dikunci admin).
     */
    public function selfUpdate(Request $request): JsonResponse
    {
        $user = auth()->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,terlambat',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $att = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->where('source', 'self_service')
            ->first();

        if (!$att) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi hari ini belum diisi atau sudah diverifikasi.',
            ], 422);
        }

        $att->update([
            'status' => $data['status'],
            'keterangan' => $data['keterangan'] ?? $att->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi diperbarui.',
            'data' => $att->fresh(),
        ]);
    }

    // ─── KEPSEK / ADMIN: REKAP ABSENSI PEGAWAI ─────────────────

    /**
     * GET /api/v1/staff/attendance/recap
     * Rekap absensi pegawai (per jabatan & per individu).
     * Akses: kepsek, admin
     */
    public function recap(Request $request): JsonResponse
    {
        $schoolId = auth()->user()->school_id;
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);
        $jabatan = $request->get('jabatan');

        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-{$daysInMonth}";

        // Per-jabatan
        $byJabatan = StaffAttendance::recapByJabatan($schoolId, $startDate, $endDate);

        // Per-individu
        $staffQuery = Staff::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('jabatan')->orderBy('nama_lengkap');

        if ($jabatan) {
            $staffQuery->where('jabatan', $jabatan);
        }

        $perStaff = $staffQuery->get()->map(function ($staff) use ($startDate, $endDate) {
            $recap = $staff->recapAttendance($startDate, $endDate);
            $recap['staff'] = [
                'id' => $staff->id,
                'nama' => $staff->nama_lengkap,
                'nip' => $staff->nip,
                'jabatan' => $staff->jabatan,
                'jabatan_label' => Staff::jabatanLabel($staff->jabatan),
            ];
            return $recap;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => ['tahun' => $year, 'bulan' => $month],
                'by_jabatan' => $byJabatan,
                'per_staff' => $perStaff,
            ],
        ]);
    }

    /**
     * GET /api/v1/staff/attendance/daily
     * Absensi pegawai untuk satu tanggal tertentu — grid view.
     * Akses: kepsek, admin
     */
    public function daily(Request $request): JsonResponse
    {
        $schoolId = auth()->user()->school_id;
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $jabatan = $request->get('jabatan');

        $staffQuery = Staff::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('jabatan')->orderBy('nama_lengkap');

        if ($jabatan) {
            $staffQuery->where('jabatan', $jabatan);
        }

        $staffList = $staffQuery->get();

        $attendances = StaffAttendance::where('school_id', $schoolId)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('staff_id');

        $rows = $staffList->map(function ($staff) use ($attendances) {
            $att = $attendances->get($staff->id);
            return [
                'staff_id' => $staff->id,
                'nama' => $staff->nama_lengkap,
                'nip' => $staff->nip,
                'jabatan' => $staff->jabatan,
                'jabatan_label' => Staff::jabatanLabel($staff->jabatan),
                'status' => $att?->status ?? null,
                'status_label' => $att ? StaffAttendance::statusLabel($att->status) : 'Belum',
                'check_in_time' => $att?->check_in_time,
                'check_out_time' => $att?->check_out_time,
                'keterangan' => $att?->keterangan,
                'source' => $att?->source,
            ];
        });

        $summary = StaffAttendance::statusSummary($schoolId, $tanggal, $tanggal);

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $tanggal,
                'summary' => $summary,
                'records' => $rows,
            ],
        ]);
    }
}
