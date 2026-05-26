<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $month = sprintf('%02d', now()->month);
        $year = now()->year;

        $data = [
            'totalSiswa'      => Student::where('school_id', $schoolId)->where('status', 'aktif')->count(),
            'totalGuruStaff'  => User::where('school_id', $schoolId)
                                    ->whereIn('role', ['guru', 'walikelas', 'kepsek', 'admin', 'tata_usaha', 'bk', 'perpustakaan'])
                                    ->where('is_active', true)->count(),
            'totalRombel'     => SchoolClass::where('school_id', $schoolId)->where('is_active', true)->count(),
            'pemasukanBulanIni' => Payment::where('school_id', $schoolId)
                                    ->where('status', 'verified')
                                    ->whereRaw("strftime('%m', payment_date) = ?", [$month])
                                    ->whereRaw("strftime('%Y', payment_date) = ?", [(string)$year])
                                    ->sum('amount'),
            'tunggakan' => Invoice::where('school_id', $schoolId)
                                    ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                                    ->sum('total') 
                              - Payment::where('school_id', $schoolId)
                                    ->where('status', 'verified')
                                    ->sum('amount'),
        ];

        // Attendance hari ini
        $semesterId = Semester::where('is_active', true)->value('id');
        $hariIni = now()->format('Y-m-d');
        $data['attendance_hari_ini'] = [];
        if ($semesterId) {
            $todayAtt = Attendance::where('semester_id', $semesterId)
                ->whereDate('tanggal', $hariIni)
                ->selectRaw("status, COUNT(*) as count")
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $data['attendance_hari_ini'] = [
                'hadir' => ($todayAtt['hadir'] ?? 0) + ($todayAtt['terlambat'] ?? 0),
                'izin' => $todayAtt['izin'] ?? 0,
                'sakit' => $todayAtt['sakit'] ?? 0,
                'alfa' => ($todayAtt['alfa'] ?? 0) + ($todayAtt['tidak_hadir'] ?? 0),
                'total' => array_sum($todayAtt),
            ];
        }

        // Attendance bulan ini (summary)
        $data['attendance_bulan_ini'] = [];
        $data['attendance_per_kelas'] = [];
        if ($semesterId) {
            $monthAtt = Attendance::where('semester_id', $semesterId)
                ->whereRaw("strftime('%m', tanggal) = ?", [$month])
                ->whereRaw("strftime('%Y', tanggal) = ?", [(string)$year])
                ->selectRaw("status, COUNT(*) as count")
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalMonth = array_sum($monthAtt);
            $hadirMonth = ($monthAtt['hadir'] ?? 0) + ($monthAtt['terlambat'] ?? 0);

            $data['attendance_bulan_ini'] = [
                'hadir' => $monthAtt['hadir'] ?? 0,
                'terlambat' => $monthAtt['terlambat'] ?? 0,
                'izin' => $monthAtt['izin'] ?? 0,
                'sakit' => $monthAtt['sakit'] ?? 0,
                'alfa' => ($monthAtt['alfa'] ?? 0) + ($monthAtt['tidak_hadir'] ?? 0),
                'total' => $totalMonth,
                'persentase_kehadiran' => $totalMonth > 0 ? round(($hadirMonth / $totalMonth) * 100, 1) : 0,
            ];

            // Per-kelas attendance bulan ini (untuk kepsek/admin)
            $classes = SchoolClass::where('school_id', $schoolId)
                ->where('is_active', true)
                ->with(['students' => fn($q) => $q->where('status', 'aktif')])
                ->orderBy('tingkat')
                ->orderBy('code')
                ->get();

            $data['attendance_per_kelas'] = $classes->map(function ($class) use ($semesterId, $month, $year) {
                $studentIds = $class->students->pluck('id');
                $counts = Attendance::whereIn('student_id', $studentIds)
                    ->where('semester_id', $semesterId)
                    ->whereRaw("strftime('%m', tanggal) = ?", [$month])
                    ->whereRaw("strftime('%Y', tanggal) = ?", [(string)$year])
                    ->selectRaw("status, COUNT(*) as count")
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $total = array_sum($counts);
                $hadir = ($counts['hadir'] ?? 0) + ($counts['terlambat'] ?? 0);

                return (object)[
                    'class_id' => $class->id,
                    'code' => $class->code,
                    'name' => $class->name,
                    'tingkat' => $class->tingkat,
                    'total_students' => $class->students->count(),
                    'hadir' => $counts['hadir'] ?? 0,
                    'terlambat' => $counts['terlambat'] ?? 0,
                    'izin' => $counts['izin'] ?? 0,
                    'sakit' => $counts['sakit'] ?? 0,
                    'alfa' => ($counts['alfa'] ?? 0) + ($counts['tidak_hadir'] ?? 0),
                    'total' => $total,
                    'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                ];
            })->values();
        }

        // Staff Attendance bulan ini (untuk kepsek/admin)
        $data['staff_attendance_bulan_ini'] = StaffAttendance::statusSummary(
            $schoolId,
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        );

        // Staff Attendance Per Jabatan
        $data['staff_attendance_by_jabatan'] = StaffAttendance::recapByJabatan(
            $schoolId,
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        );

        return view('backend.dashboard', $data);
    }
}
