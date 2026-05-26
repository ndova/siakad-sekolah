<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Semester;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * Jadwal mengajar guru hari ini
     */
    public function jadwalHariIni(Request $request): JsonResponse
    {
        $user = $request->user();
        $hariIni = now()->format('Y-m-d');

        $classSubjects = ClassSubject::with(['subject', 'schoolClass', 'schoolClass.students' => fn($q) => $q->where('status', 'aktif')])
            ->where('teacher_id', $user->id)
            ->get();

        $semesterId = Semester::where('is_active', true)->value('id');

        $result = $classSubjects->map(function ($cs) use ($hariIni, $semesterId) {
            $count = Attendance::where('class_subject_id', $cs->id)
                ->where('semester_id', $semesterId)
                ->where('tanggal', $hariIni)
                ->count();

            $totalStudent = $cs->schoolClass->students->count();

            return [
                'class_subject_id' => $cs->id,
                'subject' => $cs->subject->name,
                'class' => $cs->schoolClass->code,
                'tingkat' => $cs->schoolClass->tingkat,
                'total_students' => $totalStudent,
                'filled' => $count,
                'status' => $count >= $totalStudent ? 'done' : ($count > 0 ? 'partial' : 'pending'),
                'label' => $count >= $totalStudent ? '✅ Selesai' : ($count > 0 ? '🟡 Sebagian' : '⬜ Belum'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'date' => $hariIni,
            'total' => $result->count(),
            'done' => $result->where('status', 'done')->count(),
            'data' => $result,
        ]);
    }

    /**
     * Daftar presensi per class_subject & tanggal
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subject,id',
            'date' => 'required|date',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        $classSubject = ClassSubject::with(['subject', 'schoolClass'])->findOrFail($validated['class_subject_id']);
        $semesterId = $validated['semester_id'] ?? Semester::where('is_active', true)->value('id');

        $students = Student::with(['user'])
            ->where('class_id', $classSubject->class_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $attendances = Attendance::where('class_subject_id', $classSubject->id)
            ->where('semester_id', $semesterId)
            ->where('tanggal', $validated['date'])
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $studentList = $students->map(function ($student) use ($attendances) {
            $att = $attendances->get($student->id);
            return [
                'student_id' => $student->id,
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'status' => $att?->status ?? 'belum',
                'label' => Attendance::statusLabel($att?->status ?? 'belum'),
                'color' => Attendance::statusColor($att?->status ?? 'belum'),
                'icon' => Attendance::statusIcon($att?->status ?? 'belum'),
                'keterangan' => $att?->keterangan,
                'created_at' => $att?->created_at?->toISOString(),
            ];
        });

        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alfa' => $attendances->where('status', 'alfa')->count() + $attendances->where('status', 'tidak_hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'belum' => $students->count() - $attendances->count(),
            'total' => $students->count(),
        ];

        return response()->json([
            'success' => true,
            'class_subject' => [
                'id' => $classSubject->id,
                'subject' => $classSubject->subject->name,
                'class' => $classSubject->schoolClass->code,
            ],
            'date' => $validated['date'],
            'summary' => $summary,
            'data' => $studentList,
        ]);
    }

    /**
     * Simpan satu presensi
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_subject_id' => 'required|exists:class_subject,id',
            'semester_id' => 'required|exists:semesters,id',
            'tanggal' => 'required|date',
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa', 'tidak_hadir', 'terlambat'])],
            'keterangan' => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'class_subject_id' => $validated['class_subject_id'],
                'semester_id' => $validated['semester_id'],
                'tanggal' => $validated['tanggal'],
            ],
            [
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan',
            'data' => [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'label' => Attendance::statusLabel($attendance->status),
                'color' => Attendance::statusColor($attendance->status),
                'icon' => Attendance::statusIcon($attendance->status),
                'keterangan' => $attendance->keterangan,
            ],
        ], 201);
    }

    /**
     * Simpan presensi massal (bulk)
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subject,id',
            'semester_id' => 'required|exists:semesters,id',
            'tanggal' => 'required|date',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa', 'tidak_hadir', 'terlambat'])],
            'attendances.*.keterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $saved = 0;
            foreach ($validated['attendances'] as $item) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'class_subject_id' => $validated['class_subject_id'],
                        'semester_id' => $validated['semester_id'],
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'status' => $item['status'],
                        'keterangan' => $item['keterangan'] ?? null,
                        'created_by' => $user->id,
                    ]
                );
                $saved++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$saved presensi berhasil disimpan",
                'saved_count' => $saved,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan presensi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Rekap presensi per kelas (untuk wali kelas)
     */
    public function recap(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2099',
        ]);

        $semesterId = $validated['semester_id'] ?? Semester::where('is_active', true)->value('id');
        $semester = Semester::with('academicYear')->findOrFail($semesterId);
        $class = SchoolClass::with('waliKelas')->findOrFail($validated['class_id']);

        $students = Student::where('class_id', $class->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $attendanceQuery = Attendance::where('semester_id', $semesterId)
            ->whereIn('student_id', $students->pluck('id'));

        if (!empty($validated['month'])) {
            $attendanceQuery->whereRaw("strftime('%m', tanggal) = ?", [sprintf('%02d', $validated['month'])]);
        }
        if (!empty($validated['year'])) {
            $attendanceQuery->whereRaw("strftime('%Y', tanggal) = ?", [(string)$validated['year']]);
        }

        $allAttendances = $attendanceQuery->get()->groupBy('student_id');

        $recap = $students->map(function ($student) use ($allAttendances) {
            $records = $allAttendances->get($student->id, collect());
            $total = $records->count();

            return [
                'student_id' => $student->id,
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'hadir' => $records->where('status', 'hadir')->count(),
                'izin' => $records->where('status', 'izin')->count(),
                'sakit' => $records->where('status', 'sakit')->count(),
                'alfa' => $records->where('status', 'alfa')->count() + $records->where('status', 'tidak_hadir')->count(),
                'terlambat' => $records->where('status', 'terlambat')->count(),
                'total' => $total,
                'persentase_hadir' => $total > 0 ? round((($records->where('status', 'hadir')->count() + $records->where('status', 'terlambat')->count()) / $total) * 100, 1) : 0,
            ];
        });

        // Summary seluruh kelas
        $all = $allAttendances->flatten(1);
        $totalAll = $all->count();

        return response()->json([
            'success' => true,
            'class' => [
                'name' => $class->code,
                'tingkat' => $class->tingkat,
                'wali_kelas' => $class->waliKelas->name ?? '',
            ],
            'semester' => [
                'label' => 'Semester ' . $semester->semester_number,
                'tahun_ajaran' => $semester->academicYear->year_label ?? '',
            ],
            'filter' => [
                'month' => $validated['month'] ?? null,
                'year' => $validated['year'] ?? null,
            ],
            'class_summary' => [
                'hadir' => $all->where('status', 'hadir')->count(),
                'izin' => $all->where('status', 'izin')->count(),
                'sakit' => $all->where('status', 'sakit')->count(),
                'alfa' => $all->where('status', 'alfa')->count() + $all->where('status', 'tidak_hadir')->count(),
                'terlambat' => $all->where('status', 'terlambat')->count(),
                'total' => $totalAll,
                'persentase_hadir' => $totalAll > 0 ? round((($all->whereIn('status', ['hadir', 'terlambat'])->count()) / $totalAll) * 100, 1) : 0,
            ],
            'data' => $recap,
        ]);
    }

    /**
     * Update presensi (koreksi)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa', 'tidak_hadir', 'terlambat'])],
            'keterangan' => 'nullable|string|max:255',
        ]);

        $attendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dikoreksi',
            'data' => [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'label' => Attendance::statusLabel($attendance->status),
                'color' => Attendance::statusColor($attendance->status),
                'keterangan' => $attendance->keterangan,
            ],
        ]);
    }
}
