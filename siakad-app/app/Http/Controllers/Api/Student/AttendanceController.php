<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Daftar presensi siswa per hari (dengan filter dan calendar support)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::with('class')->where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->value('id');
        $perPage = $request->per_page ?? 31;

        $attendances = Attendance::with(['classSubject.subject', 'classSubject.teacher'])
            ->where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->when($request->date_from, fn($q) => $q->whereDate('tanggal', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('tanggal', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attendances->through(function ($att) {
                return [
                    'id' => $att->id,
                    'tanggal' => $att->tanggal->format('Y-m-d'),
                    'hari' => $att->tanggal->isoFormat('dddd'),
                    'subject' => $att->classSubject?->subject?->name,
                    'teacher' => $att->classSubject?->teacher?->name,
                    'status' => $att->status,
                    'label' => Attendance::statusLabel($att->status),
                    'color' => Attendance::statusColor($att->status),
                    'icon' => Attendance::statusIcon($att->status),
                    'keterangan' => $att->keterangan,
                    'jam' => $att->created_at?->format('H:i'),
                ];
            }),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }

    /**
     * Rekap presensi siswa per semester (dengan per-mapel dan per-bulan breakdown)
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::with('class')->where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->value('id');
        $semester = Semester::with('academicYear')->find($semesterId);

        $attendanceData = Attendance::with('classSubject.subject')
            ->where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->get();

        // Overall summary
        $summary = Attendance::recapForStudent($student->id, $semesterId);

        // Per-mapel breakdown
        $perMapel = $attendanceData->groupBy('class_subject_id')->map(function ($items, $csId) {
            $first = $items->first();
            $total = $items->count();
            return [
                'subject' => $first->classSubject?->subject?->name ?? '-',
                'hadir' => $items->where('status', 'hadir')->count(),
                'izin' => $items->where('status', 'izin')->count(),
                'sakit' => $items->where('status', 'sakit')->count(),
                'alfa' => $items->where('status', 'alfa')->count() + $items->where('status', 'tidak_hadir')->count(),
                'terlambat' => $items->where('status', 'terlambat')->count(),
                'total' => $total,
                'persentase_hadir' => $total > 0 ? round((($items->where('status', 'hadir')->count() + $items->where('status', 'terlambat')->count()) / $total) * 100, 1) : 0,
            ];
        })->values();

        // Per-bulan breakdown
        $perBulan = $attendanceData->groupBy(fn($a) => $a->tanggal->format('Y-m'))->map(function ($items, $month) {
            $total = $items->count();
            return [
                'bulan' => $month,
                'hadir' => $items->where('status', 'hadir')->count(),
                'izin' => $items->where('status', 'izin')->count(),
                'sakit' => $items->where('status', 'sakit')->count(),
                'alfa' => $items->where('status', 'alfa')->count() + $items->where('status', 'tidak_hadir')->count(),
                'terlambat' => $items->where('status', 'terlambat')->count(),
                'total' => $total,
                'persentase_hadir' => $total > 0 ? round((($items->where('status', 'hadir')->count() + $items->where('status', 'terlambat')->count()) / $total) * 100, 1) : 0,
            ];
        })->sortKeys()->values();

        // Calendar data: all dates with attendance for current month
        $yearMonth = $request->year_month ?? now()->format('Y-m');
        [$y, $m] = explode('-', $yearMonth);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$m, (int)$y);

        $calendarData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%s-%s-%02d', $y, $m, $d);
            $dayAtt = $attendanceData->filter(fn($a) => $a->tanggal->format('Y-m-d') === $date);
            $calendarData[] = [
                'date' => $date,
                'day' => $d,
                'has_data' => $dayAtt->isNotEmpty(),
                'records' => $dayAtt->map(fn($a) => [
                    'status' => $a->status,
                    'subject' => $a->classSubject?->subject?->name,
                    'label' => Attendance::statusLabel($a->status),
                    'color' => Attendance::statusColor($a->status),
                    'icon' => Attendance::statusIcon($a->status),
                ])->values(),
            ];
        }

        return response()->json([
            'success' => true,
            'student' => [
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'kelas' => $student->class->code ?? '',
            ],
            'semester' => $semester ? [
                'id' => $semester->id,
                'label' => 'Semester ' . $semester->semester_number,
                'tahun_ajaran' => $semester->academicYear->year_label ?? '',
            ] : null,
            'year_month' => $yearMonth,
            'summary' => $summary,
            'per_mapel' => $perMapel,
            'per_bulan' => $perBulan,
            'calendar' => $calendarData,
        ]);
    }
}
