<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\Staff;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceManualController extends Controller
{
    // ─── ABSENSI MANUAL SISWA ───────────────────────────────

    public function siswaForm(Request $request)
    {
        $user = auth()->user();
        $classId = $request->get('class_id');
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $classQuery = SchoolClass::where('school_id', $this->schoolId())
            ->where('is_active', true);

        // Guru hanya lihat kelas yang diampu
        if ($user->role === 'guru') {
            $taughtClassIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $classQuery->whereIn('id', $taughtClassIds);
            if ($classId && !in_array($classId, $taughtClassIds->toArray())) {
                $classId = $taughtClassIds->first();
            }
        }

        $classes = $classQuery->orderBy('code')->get();

        $students = collect();
        $existingAttendance = collect();

        if ($classId) {
            $students = Student::where('class_id', $classId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap')
                ->get();

            $existingAttendance = Attendance::whereIn('student_id', $students->pluck('id'))
                ->whereDate('tanggal', $tanggal)
                ->get()
                ->keyBy('student_id');
        }

        return view('backend.attendance.siswa_manual', compact(
            'classes', 'classId', 'tanggal', 'students', 'existingAttendance'
        ));
    }

    public function siswaStore(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'tanggal' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'required|in:hadir,izin,sakit,alfa,terlambat',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        $semester = Semester::whereHas('academicYear', fn($q) => $q->where('school_id', $this->schoolId()))
            ->where('is_active', true)
            ->first();

        if (!$semester) {
            return back()->with('error', 'Tidak ada semester aktif.');
        }

        $count = 0;
        foreach ($validated['status'] as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'tanggal' => $validated['tanggal'],
                ],
                [
                    'semester_id' => $semester->id,
                    'status' => $status,
                    'keterangan' => $validated['keterangan'][$studentId] ?? null,
                    'created_by' => auth()->id(),
                ]
            );
            $count++;
        }

        return redirect()->route('attendance.siswa.manual', [
            'class_id' => $validated['class_id'],
            'tanggal' => $validated['tanggal'],
        ])->with('success', "{$count} presensi siswa berhasil disimpan.");
    }

    // ─── ABSENSI MANUAL PEGAWAI ─────────────────────────────

    public function pegawaiForm(Request $request)
    {
        // Guru tidak boleh akses absensi pegawai
        if (auth()->user()->role === 'guru') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $jabatan = $request->get('jabatan');

        $staffQuery = Staff::where('school_id', $this->schoolId())
            ->where('is_active', true);

        if ($jabatan) {
            $staffQuery->where('jabatan', $jabatan);
        }

        $staffList = $staffQuery->orderBy('nama_lengkap')->get();

        $existingAttendance = StaffAttendance::whereIn('staff_id', $staffList->pluck('id'))
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('staff_id');

        $jabatans = Staff::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->distinct()
            ->pluck('jabatan')
            ->filter()
            ->values();

        return view('backend.attendance.pegawai_manual', compact(
            'staffList', 'tanggal', 'jabatan', 'jabatans', 'existingAttendance'
        ));
    }

    public function pegawaiStore(Request $request)
    {
        if (auth()->user()->role === 'guru') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'required|in:hadir,izin,sakit,alfa,terlambat',
            'check_in_time' => 'nullable|array',
            'check_in_time.*' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|array',
            'check_out_time.*' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        $count = 0;
        foreach ($validated['status'] as $staffId => $status) {
            StaffAttendance::updateOrCreate(
                [
                    'staff_id' => $staffId,
                    'tanggal' => $validated['tanggal'],
                ],
                [
                    'school_id' => $this->schoolId(),
                    'status' => $status,
                    'check_in_time' => $validated['check_in_time'][$staffId] ?? null,
                    'check_out_time' => $validated['check_out_time'][$staffId] ?? null,
                    'keterangan' => $validated['keterangan'][$staffId] ?? null,
                    'source' => 'manual',
                    'created_by' => auth()->id(),
                ]
            );
            $count++;
        }

        return redirect()->route('attendance.pegawai.manual', [
            'tanggal' => $validated['tanggal'],
            'jabatan' => $request->get('jabatan'),
        ])->with('success', "{$count} presensi pegawai berhasil disimpan.");
    }

    // ─── HELPER ─────────────────────────────────────────────

    protected function schoolId(): string
    {
        return \App\Services\SchoolService::get()?->id ?? '';
    }
}
