<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Attendance;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\ClassSubject;
use App\Models\LearningObjectiveSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard siswa — ringkasan nilai, jadwal ujian, tagihan
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::with(['class:id,code,tingkat,wali_kelas_id', 'class.waliKelas:id,name'])
            ->where('user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $activeSemester = Semester::where('is_active', true)->first();

        // Ringkasan nilai semester ini
        $nilaiRataRata = null;
        if ($activeSemester) {
            $nilaiRataRata = Grade::where('student_id', $student->id)
                ->where('semester_id', $activeSemester->id)
                ->avg('nilai');
        }

        // Jumlah kehadiran
        $totalHadir = Attendance::where('student_id', $student->id)
            ->where('status', 'hadir')
            ->count();
        $totalAttendance = Attendance::where('student_id', $student->id)->count();

        // Tagihan menunggu
        $pendingInvoices = Invoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('remaining');

        // Ujian mendatang
        $upcomingExams = Exam::where(function ($q) {
            $q->where('status', 'published')->orWhere('status', 'ongoing');
        })
            ->whereJsonContains('class_ids', $student->class_id)
            ->where('start_time', '>', now()->subHours(1))
            ->orderBy('start_time')
            ->limit(5)
            ->get(['id', 'title', 'type', 'start_time', 'end_time', 'duration', 'total_questions', 'total_score']);

        // Jadwal hari ini
        $todaySchedule = ClassSubject::with(['subject:id,name,code', 'teacher:id,name'])
            ->where('class_id', $student->class_id)
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'nama' => $student->nama_lengkap,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'kelas' => $student->class?->code,
                    'tingkat' => $student->class?->tingkat,
                    'wali_kelas' => $student->class?->waliKelas?->name,
                ],
                'akademik' => [
                    'nilai_rata_rata' => $nilaiRataRata !== null ? round((float) $nilaiRataRata, 2) : null,
                    'total_hadir' => $totalHadir,
                    'total_pertemuan' => $totalAttendance,
                    'persentase_hadir' => $totalAttendance > 0 ? round(($totalHadir / $totalAttendance) * 100, 1) : 0,
                ],
                'keuangan' => [
                    'tunggakan' => (float) ($pendingInvoices ?? 0),
                ],
                'ujian_mendatang' => $upcomingExams->map(fn($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'type' => $e->type,
                    'start_time' => $e->start_time?->toISOString(),
                    'end_time' => $e->end_time?->toISOString(),
                    'duration' => $e->duration,
                    'total_questions' => $e->total_questions,
                ]),
                'jadwal_hari_ini' => $todaySchedule->map(fn($cs) => [
                    'subject' => $cs->subject?->name,
                    'teacher' => $cs->teacher?->name,
                ]),
            ],
        ]);
    }

    /**
     * Rapor siswa per semester
     */
    public function report(Request $request, string $semesterId): JsonResponse
    {
        $user = $request->user();
        $student = Student::with('class')->where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $semester = Semester::with('academicYear')->findOrFail($semesterId);

        $classSubjects = ClassSubject::with(['subject', 'teacher'])
            ->where('class_id', $student->class_id)
            ->get();

        $losSubjects = LearningObjectiveSubject::with('learningObjective')
            ->whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->where('semester_id', $semester->id)
            ->get()
            ->groupBy('class_subject_id');

        $reports = Report::with('classSubject.subject')
            ->where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->where('is_locked', true)
            ->get();

        $grades = Grade::where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->get()
            ->groupBy('class_subject_id');

        $attSummary = [
            'hadir' => Attendance::where('student_id', $student->id)->where('semester_id', $semester->id)->where('status', 'hadir')->count(),
            'izin' => Attendance::where('student_id', $student->id)->where('semester_id', $semester->id)->where('status', 'izin')->count(),
            'sakit' => Attendance::where('student_id', $student->id)->where('semester_id', $semester->id)->where('status', 'sakit')->count(),
            'tidak_hadir' => Attendance::where('student_id', $student->id)->where('semester_id', $semester->id)->where('status', 'tidak_hadir')->count(),
        ];

        $p5Projects = P5Project::where('semester_id', $semester->id)
            ->whereJsonContains('class_ids', $student->class_id)
            ->get();

        $p5Assessments = P5Assessment::whereIn('p5_project_id', $p5Projects->pluck('id'))
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('p5_project_id');

        $subjects = $classSubjects->map(function ($cs) use ($grades, $reports, $losSubjects) {
            $report = $reports->firstWhere('class_subject_id', $cs->id);
            $subjectGrades = $grades->get($cs->id, collect());

            // Per TP
            $tps = $losSubjects->get($cs->id, collect())->map(function ($los) use ($subjectGrades) {
                $g = $subjectGrades->firstWhere('learning_objective_id', $los->learning_objective_id);
                return [
                    'tp_code' => $los->learningObjective->code,
                    'tp_desc' => $los->learningObjective->description,
                    'nilai' => $g ? (float) $g->nilai : null,
                ];
            });

            return [
                'subject' => $cs->subject->name,
                'teacher' => $cs->teacher?->name,
                'kkm' => $cs->kkm,
                'nilai_akhir' => $report?->nilai_akhir !== null ? (float) $report->nilai_akhir : null,
                'predikat' => $report?->predikat,
                'tp_grades' => $tps,
            ];
        });

        $p5 = $p5Projects->map(function ($project) use ($p5Assessments) {
            $ass = $p5Assessments->get($project->id);
            return [
                'tema' => $project->tema,
                'judul' => $project->judul,
                'dimensi' => $ass ? [
                    'd1' => $ass->dimensi_1, 'd2' => $ass->dimensi_2, 'd3' => $ass->dimensi_3,
                    'd4' => $ass->dimensi_4, 'd5' => $ass->dimensi_5, 'd6' => $ass->dimensi_6,
                ] : null,
                'catatan_proses' => $ass?->catatan_proses,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'nama' => $student->nama_lengkap,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'kelas' => $student->class->code ?? '',
                ],
                'semester' => [
                    'label' => 'Semester ' . $semester->semester_number,
                    'tahun_ajaran' => $semester->academicYear->year_label ?? '',
                ],
                'subjects' => $subjects,
                'attendance' => $attSummary,
                'p5' => $p5,
                'is_locked' => $reports->isNotEmpty(),
            ],
        ]);
    }

    /**
     * Jadwal ujian siswa
     */
    public function examSchedule(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $exams = Exam::with(['subject:id,name,code'])
            ->whereIn('status', ['published', 'ongoing', 'finished'])
            ->whereJsonContains('class_ids', $student->class_id)
            ->orderBy('start_time')
            ->get()
            ->map(function ($exam) use ($student) {
                $session = ExamSession::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->first();
                $result = ExamResult::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $exam->id,
                    'code' => $exam->code,
                    'title' => $exam->title,
                    'type' => $exam->type,
                    'subject' => $exam->subject?->name,
                    'start_time' => $exam->start_time?->toISOString(),
                    'end_time' => $exam->end_time?->toISOString(),
                    'duration' => $exam->duration,
                    'total_questions' => $exam->total_questions,
                    'total_score' => $exam->total_score,
                    'status' => $exam->status,
                    'session_status' => $session?->status,
                    'my_score' => $result?->total_score !== null ? (float) $result->total_score : null,
                    'is_passed' => $result?->is_passed,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $exams,
        ]);
    }

    /**
     * Riwayat pembayaran siswa
     */
    public function payments(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $invoices = Invoice::with(['items', 'payments'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'items' => $inv->items->map(fn($i) => $i->fee_name),
                    'total' => (float) $inv->total,
                    'paid' => (float) $inv->paid_amount,
                    'remaining' => $inv->remaining,
                    'status' => $inv->status,
                    'due_date' => $inv->due_date?->format('Y-m-d'),
                    'paid_at' => $inv->paid_at?->toISOString(),
                    'payments' => $inv->payments->map(fn($p) => [
                        'id' => $p->id,
                        'amount' => (float) $p->amount,
                        'status' => $p->status,
                        'payment_date' => $p->payment_date?->format('Y-m-d'),
                        'channel' => $p->payment_channel,
                    ]),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }
}
