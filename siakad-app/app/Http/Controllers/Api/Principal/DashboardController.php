<?php

namespace App\Http\Controllers\Api\Principal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $semesterId = Semester::where('is_active', true)->value('id');

        // Ringkasan utama
        $totalSiswa = Student::where('status', 'aktif')->count();
        $totalGuru = User::whereIn('role', ['guru', 'walikelas'])->where('is_active', true)->count();
        $totalPemasukan = Payment::where('status', 'verified')->sum('amount');
        $tunggakan = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('total');

        // Statistik presensi seluruh sekolah
        $attSummary = [];
        if ($semesterId) {
            $attCounts = Attendance::where('semester_id', $semesterId)
                ->selectRaw("status, COUNT(*) as count")
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalAtt = array_sum($attCounts);
            $hadir = ($attCounts['hadir'] ?? 0) + ($attCounts['terlambat'] ?? 0);

            $attSummary = [
                'hadir' => $attCounts['hadir'] ?? 0,
                'izin' => $attCounts['izin'] ?? 0,
                'sakit' => $attCounts['sakit'] ?? 0,
                'alfa' => ($attCounts['alfa'] ?? 0) + ($attCounts['tidak_hadir'] ?? 0),
                'terlambat' => $attCounts['terlambat'] ?? 0,
                'total' => $totalAtt,
                'persentase_kehadiran' => $totalAtt > 0 ? round(($hadir / $totalAtt) * 100, 1) : 0,
            ];
        }

        // Tingkat kehadiran per kelas
        $perKelas = [];
        if ($semesterId) {
            $classes = SchoolClass::with(['students' => fn($q) => $q->where('status', 'aktif')])
                ->where('is_active', true)
                ->orderBy('tingkat')
                ->orderBy('code')
                ->get();

            $perKelas = $classes->map(function ($class) use ($semesterId) {
                $studentIds = $class->students->pluck('id');
                $counts = Attendance::whereIn('student_id', $studentIds)
                    ->where('semester_id', $semesterId)
                    ->selectRaw("status, COUNT(*) as count")
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $total = array_sum($counts);
                $hadir = ($counts['hadir'] ?? 0) + ($counts['terlambat'] ?? 0);

                return [
                    'class_id' => $class->id,
                    'class_code' => $class->code,
                    'tingkat' => $class->tingkat,
                    'total_students' => $class->students->count(),
                    'hadir' => $counts['hadir'] ?? 0,
                    'izin' => $counts['izin'] ?? 0,
                    'sakit' => $counts['sakit'] ?? 0,
                    'alfa' => ($counts['alfa'] ?? 0) + ($counts['tidak_hadir'] ?? 0),
                    'terlambat' => $counts['terlambat'] ?? 0,
                    'total' => $total,
                    'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                    'status' => $total > 0 ? (round(($hadir / $total) * 100, 1) >= 90 ? 'good' : (round(($hadir / $total) * 100, 1) >= 75 ? 'warning' : 'danger')) : 'unknown',
                ];
            })->values();
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_siswa' => $totalSiswa,
                'total_guru' => $totalGuru,
                'total_pemasukan' => (float) $totalPemasukan,
                'tunggakan' => (float) $tunggakan,
            ],
            'attendance' => $attSummary,
            'attendance_per_class' => $perKelas,
        ]);
    }
}
