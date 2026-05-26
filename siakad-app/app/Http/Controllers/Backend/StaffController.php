<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Services\StaffService;
use App\Traits\HasSchoolScope;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    use HasSchoolScope;

    private function service(): StaffService
    {
        return new StaffService($this->schoolId());
    }

    // ─── DAFTAR STAFF ──────────────────────────────────────────

    public function index(Request $request)
    {
        $svc     = $this->service();
        $perPage = $this->perPage($request);

        return view('backend.staff.index', [
            'staffList'          => $svc->staffQuery(
                $request->search,
                $request->jabatan,
                $request->get('status_aktif')
            )->paginate($perPage)->withQueryString(),
            'jabatanList'        => Staff::jabatanList(),
            'totalByJabatan'     => $svc->totalByJabatan(),
            'usersWithoutStaff'  => $svc->usersWithoutStaff(),
            'perPage'            => $perPage,
        ]);
    }

    // ─── CRUD STAFF ────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $this->validateStaff($request);
        $data['school_id'] = $this->schoolId();
        $data['id'] = Str::uuid();
        $data['is_active'] = true;

        if (!empty($data['user_id'])) {
            \App\Models\User::where('id', $data['user_id'])
                ->update(['role' => Staff::mapJabatanToRole($data['jabatan'])]);
        }

        Staff::create($data);

        return redirect()->route('staff.index')->with('success', 'Staff berhasil ditambahkan.');
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $this->validateStaff($request);

        if ($staff->user_id && $staff->jabatan !== $data['jabatan']) {
            \App\Models\User::where('id', $staff->user_id)
                ->update(['role' => Staff::mapJabatanToRole($data['jabatan'])]);
        }

        $staff->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff berhasil diperbarui.');
    }

    public function toggleActive(Staff $staff)
    {
        $staff->update(['is_active' => !$staff->is_active]);

        return redirect()->route('staff.index')->with(
            'success',
            $staff->is_active ? 'Staff diaktifkan.' : 'Staff dinonaktifkan.'
        );
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff berhasil dihapus.');
    }

    // ─── ABSENSI STAFF ─────────────────────────────────────────

    public function attendanceGrid(Request $request)
    {
        $year    = $request->integer('year', now()->year);
        $month   = $request->integer('month', now()->month);
        $jabatan = $request->get('jabatan');

        return view('backend.staff.attendance-grid', array_merge(
            $this->service()->attendanceGrid($year, $month, $jabatan),
            compact('year', 'month', 'jabatan'),
            ['jabatanList' => Staff::jabatanList()],
        ));
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'staff_id'       => 'required|exists:staff,id',
            'tanggal'        => 'required|date',
            'status'         => 'required|in:hadir,izin,sakit,alfa,terlambat',
            'check_in_time'  => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'keterangan'     => 'nullable|string|max:255',
            'source'         => 'nullable|in:manual,self_service,mesin_absen',
        ]);

        $this->service()->storeAttendance($data);

        return back()->with('success', 'Absensi disimpan.');
    }

    public function bulkAttendance(Request $request)
    {
        $data = $request->validate([
            'tanggal'                 => 'required|date',
            'records'                 => 'required|array',
            'records.*.staff_id'      => 'required|exists:staff,id',
            'records.*.status'        => 'required|in:hadir,izin,sakit,alfa,terlambat',
            'records.*.keterangan'    => 'nullable|string|max:255',
        ]);

        $this->service()->bulkStoreAttendance($data['tanggal'], $data['records']);

        return back()->with('success', 'Absensi massal disimpan.');
    }

    // ─── REKAP ABSENSI ─────────────────────────────────────────

    public function attendanceRecap(Request $request)
    {
        $year    = $request->integer('year', now()->year);
        $month   = $request->integer('month', now()->month);
        $jabatan = $request->get('jabatan');

        return view('backend.staff.attendance-recap', array_merge(
            $this->service()->attendanceRecap($year, $month, $jabatan),
            compact('year', 'month', 'jabatan'),
            ['jabatanList' => Staff::jabatanList()],
        ));
    }

    // ─── IMPORT MESIN ABSEN ────────────────────────────────────

    public function importForm()
    {
        return view('backend.staff.import-attendance');
    }

    public function importAttendance(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        ['imported' => $i, 'skipped' => $s] = $this->service()
            ->importFromCSV($request->file('file')->getRealPath());

        return back()->with('success', "Import selesai: {$i} record berhasil, {$s} dilewati.");
    }

    // ─── EKSPOR ────────────────────────────────────────────────

    public function exportAttendance(Request $request)
    {
        $year  = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);
        $rows  = $this->service()->exportRows($year, $month);

        $filename = "rekap-absensi-staff-{$year}-{$month}.csv";

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, ['Nama', 'NIP', 'Jabatan', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alfa', 'Total', 'Persentase Kehadiran']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['nama'], $r['nip'], $r['jabatan'],
                    $r['hadir'], $r['terlambat'], $r['izin'],
                    $r['sakit'], $r['alfa'], $r['total'],
                    $r['persentase_hadir'] . '%',
                ]);
            }
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── PER-PAGE ───────────────────────────────────────────────

    private function perPage(Request $request): int
    {
        return in_array((int) $request->get('per_page'), [10, 20, 50, 100])
            ? (int) $request->get('per_page')
            : 20;
    }

    // ─── VALIDATION ─────────────────────────────────────────────

    private function validateStaff(Request $request): array
    {
        return $request->validate([
            'user_id'             => 'nullable|exists:users,id',
            'nama_lengkap'        => 'required|string|max:200',
            'nip'                 => 'nullable|string|max:30',
            'nuptk'               => 'nullable|string|max:30',
            'jabatan'             => 'required|string|max:50',
            'golongan'            => 'nullable|string|max:10',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'tempat_lahir'        => 'nullable|string|max:100',
            'tanggal_lahir'       => 'nullable|date',
            'jk'                  => 'nullable|in:L,P',
            'agama'               => 'nullable|string|max:20',
            'alamat'              => 'nullable|string',
            'phone'               => 'nullable|string|max:20',
            'tanggal_masuk'       => 'nullable|date',
        ]);
    }
}
