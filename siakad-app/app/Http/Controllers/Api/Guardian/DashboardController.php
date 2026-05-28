<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\Invoice;
use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard orang tua — lihat data semua anak
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::with(['students.class'])
            ->where('user_id', $user->id)
            ->first();

        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        $children = $guardian->students->map(function ($student) {
            $activeSemester = Semester::where('is_active', true)->first();

            $nilaiRata = null;
            $totalHadir = 0;
            $totalPertemuan = 0;

            if ($activeSemester) {
                $nilaiRata = Grade::where('student_id', $student->id)
                    ->where('semester_id', $activeSemester->id)
                    ->avg('nilai');

                $totalHadir = Attendance::where('student_id', $student->id)
                    ->where('semester_id', $activeSemester->id)
                    ->where('status', 'hadir')
                    ->count();

                $totalPertemuan = Attendance::where('student_id', $student->id)
                    ->where('semester_id', $activeSemester->id)
                    ->count();
            }

            // Tagihan
            $tunggakan = Invoice::where('student_id', $student->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->sum('remaining');

            // Ujian mendatang
            $upcomingExams = Exam::where(function ($q) {
                $q->where('status', 'published')->orWhere('status', 'ongoing');
            })
                ->where('class_ids', 'LIKE', '%'.$student->class_id.'%')
                ->count();

            return [
                'id' => $student->id,
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'kelas' => $student->class->code ?? '',
                'tingkat' => $student->class->tingkat ?? '',
                'nilai_rata_rata' => $nilaiRata !== null ? round((float) $nilaiRata, 2) : null,
                'total_hadir' => $totalHadir,
                'total_pertemuan' => $totalPertemuan,
                'persentase_hadir' => $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100, 1) : 0,
                'tunggakan' => (float) ($tunggakan ?? 0),
                'ujian_mendatang' => $upcomingExams,
            ];
        });

        $totalTunggakan = $children->sum('tunggakan');

        return response()->json([
            'success' => true,
            'guardian' => [
                'nama' => $guardian->nama_lengkap,
                'hubungan' => $guardian->hubungan,
            ],
            'summary' => [
                'jumlah_anak' => $children->count(),
                'total_tunggakan' => (float) $totalTunggakan,
            ],
            'data' => $children,
        ]);
    }

    /**
     * Daftar anak (detail)
     */
    public function children(Request $request): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::with([
            'students' => function ($q) {
                $q->with(['class:id,code,tingkat', 'class.waliKelas:id,name']);
            },
        ])->where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        $children = $guardian->students->map(fn($student) => [
            'id' => $student->id,
            'nama' => $student->nama_lengkap,
            'nis' => $student->nis,
            'nisn' => $student->nisn,
            'jk' => $student->jk,
            'kelas' => $student->class->code ?? '',
            'tingkat' => $student->class->tingkat ?? '',
            'wali_kelas' => $student->class->waliKelas->name ?? '',
            'alamat' => $student->alamat,
            'tanggal_lahir' => $student->tanggal_lahir?->format('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    /**
     * Nilai anak per semester
     */
    public function childGrades(Request $request, string $student): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->first();
        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        // Pastikan siswa adalah anak dari guardian ini
        $studentData = $guardian->students()->where('students.id', $student)->first();
        if (!$studentData) {
            return response()->json(['success' => false, 'message' => 'Siswa bukan anak Anda'], 403);
        }

        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->value('id');
        if (!$semesterId) {
            return response()->json(['success' => false, 'message' => 'Tidak ada semester aktif'], 400);
        }

        $semester = Semester::with('academicYear')->findOrFail($semesterId);

        $grades = Grade::with(['classSubject.subject', 'learningObjective'])
            ->where('student_id', $student)
            ->where('semester_id', $semesterId)
            ->get()
            ->groupBy('class_subject_id');

        $subjects = $grades->map(function ($subjectGrades, $csId) {
            $first = $subjectGrades->first();
            $nilaiList = $subjectGrades->pluck('nilai')->filter(fn($n) => $n !== null);
            $rataRata = $nilaiList->count() > 0 ? round($nilaiList->avg(), 2) : null;

            $predikat = null;
            if ($rataRata !== null) {
                $predikat = match (true) {
                    $rataRata >= 90 => 'A',
                    $rataRata >= 80 => 'B',
                    $rataRata >= 70 => 'C',
                    $rataRata >= 60 => 'D',
                    default => 'E',
                };
            }

            return [
                'class_subject_id' => $csId,
                'subject' => $first->classSubject->subject->name ?? '',
                'teacher' => $first->classSubject->teacher->name ?? '',
                'kkm' => $first->classSubject->kkm ?? 70,
                'rata_rata' => $rataRata,
                'predikat' => $predikat,
                'nilai_tertinggi' => $nilaiList->max(),
                'nilai_terendah' => $nilaiList->min(),
                'jumlah_tp' => $subjectGrades->groupBy('learning_objective_id')->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'student' => [
                'nama' => $studentData->nama_lengkap,
                'kelas' => $studentData->class->code ?? '',
            ],
            'semester' => [
                'label' => 'Semester ' . $semester->semester_number,
                'tahun_ajaran' => $semester->academicYear->year_label ?? '',
            ],
            'data' => $subjects,
        ]);
    }

    /**
     * Presensi anak per semester (detail + rekap + kalender)
     */
    public function childAttendance(Request $request, string $student): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->first();
        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        $studentData = $guardian->students()->where('students.id', $student)->first();
        if (!$studentData) {
            return response()->json(['success' => false, 'message' => 'Siswa bukan anak Anda'], 403);
        }

        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->value('id');
        $semester = Semester::with('academicYear')->find($semesterId);

        // Detail per hari (paginated)
        $attendances = Attendance::with(['classSubject.subject'])
            ->where('student_id', $student)
            ->where('semester_id', $semesterId)
            ->orderBy('tanggal', 'desc')
            ->paginate($request->per_page ?? 20);

        // Rekap lengkap
        $recap = Attendance::recapForStudent($student, $semesterId);

        // Per-bulan breakdown
        $allRecords = Attendance::where('student_id', $student)
            ->where('semester_id', $semesterId)
            ->get();

        $perBulan = $allRecords->groupBy(fn($a) => $a->tanggal->format('Y-m'))->map(function ($items, $month) {
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

        // Bulan ini quick indicator
        $bulanIni = now()->format('Y-m');
        $blnNow = $perBulan->firstWhere('bulan', $bulanIni);

        // Alert jika alfa > 3 kali dalam 1 bulan terakhir
        $alfaBulanIni = $blnNow['alfa'] ?? 0;
        $alfaAlert = $alfaBulanIni >= 3;

        // Calendar bulan ini
        $yearMonth = $request->year_month ?? now()->format('Y-m');
        [$y, $m] = explode('-', $yearMonth);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$m, (int)$y);

        $calendarData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%s-%s-%02d', $y, $m, $d);
            $dayAtt = $allRecords->filter(fn($a) => $a->tanggal->format('Y-m-d') === $date);
            $statuses = $dayAtt->pluck('status')->unique()->values();
            $mainStatus = $statuses->first();
            $calendarData[] = [
                'date' => $date,
                'day' => $d,
                'has_data' => $dayAtt->isNotEmpty(),
                'main_status' => $mainStatus,
                'statuses' => $statuses,
                'label' => $mainStatus ? Attendance::statusLabel($mainStatus) : null,
                'color' => $mainStatus ? Attendance::statusColor($mainStatus) : null,
            ];
        }

        return response()->json([
            'success' => true,
            'student' => [
                'nama' => $studentData->nama_lengkap,
                'nis' => $studentData->nis,
                'kelas' => $studentData->class->code ?? '',
                'tingkat' => $studentData->class->tingkat ?? '',
            ],
            'semester' => $semester ? [
                'id' => $semester->id,
                'label' => 'Semester ' . $semester->semester_number,
                'tahun_ajaran' => $semester->academicYear->year_label ?? '',
            ] : null,
            'year_month' => $yearMonth,
            'bulan_ini' => $blnNow,
            'summary' => $recap,
            'per_bulan' => $perBulan,
            'calendar' => $calendarData,
            'alerts' => [
                'alfa_alert' => $alfaAlert,
                'alfa_count' => $alfaBulanIni,
                'alfa_message' => $alfaAlert ? "⚠️ {$studentData->nama_lengkap} sudah alfa {$alfaBulanIni} kali bulan ini. Segera hubungi wali kelas." : null,
            ],
            'data' => $attendances->through(function ($att) {
                return [
                    'id' => $att->id,
                    'tanggal' => $att->tanggal->format('Y-m-d'),
                    'hari' => $att->tanggal->isoFormat('dddd'),
                    'subject' => $att->classSubject?->subject?->name,
                    'status' => $att->status,
                    'label' => Attendance::statusLabel($att->status),
                    'color' => Attendance::statusColor($att->status),
                    'icon' => Attendance::statusIcon($att->status),
                    'keterangan' => $att->keterangan,
                ];
            }),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }
}
