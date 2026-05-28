<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\Report;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\LearningObjectiveSubject;
use App\Models\ClassSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Preview rapor untuk kelas walikelas
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cek apakah user adalah walikelas
        if (!$user->isWaliKelas()) {
            return response()->json(['success' => false, 'message' => 'Hanya wali kelas yang dapat mengakses rapor'], 403);
        }

        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $homeroomClass = SchoolClass::where('wali_kelas_id', $user->id)->first();
        if (!$homeroomClass) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki kelas wali'], 404);
        }

        $semester = Semester::with('academicYear')->findOrFail($validated['semester_id']);

        // Jika student_id disediakan, tampilkan rapor satu siswa; jika tidak, semua siswa
        $studentsQuery = Student::with(['user'])
            ->where('class_id', $homeroomClass->id)
            ->where('status', 'aktif');

        if (!empty($validated['student_id'])) {
            $studentsQuery->where('id', $validated['student_id']);
        }

        $students = $studentsQuery->orderBy('nama_lengkap')->get();

        $classSubjects = ClassSubject::with(['subject', 'teacher'])
            ->where('class_id', $homeroomClass->id)
            ->get();

        $losSubjects = LearningObjectiveSubject::with('learningObjective')
            ->whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->where('semester_id', $semester->id)
            ->get()
            ->groupBy('class_subject_id');

        $studentIds = $students->pluck('id');

        // Ambil semua nilai existing
        $allGrades = Grade::whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->where('semester_id', $semester->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy(function ($g) {
                return $g->student_id . '_' . $g->class_subject_id;
            });

        // Ambil rekap presensi per siswa
        $attendances = Attendance::whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->where('semester_id', $semester->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        // Ambil P5
        $p5Projects = P5Project::where('semester_id', $semester->id)
            ->where('class_ids', 'LIKE', '%'.$homeroomClass->id.'%')
            ->get();

        $p5Assessments = P5Assessment::whereIn('p5_project_id', $p5Projects->pluck('id'))
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        // Ambil rapor yang sudah di-lock
        $lockedReports = Report::where('semester_id', $semester->id)
            ->whereIn('student_id', $studentIds)
            ->where('is_locked', true)
            ->get()
            ->keyBy('student_id');

        $reportData = $students->map(function ($student) use ($classSubjects, $losSubjects, $allGrades, $attendances, $p5Projects, $p5Assessments, $lockedReports) {
            // Nilai per mata pelajaran
            $subjects = $classSubjects->map(function ($cs) use ($student, $losSubjects, $allGrades) {
                $key = $student->id . '_' . $cs->id;
                $grades = $allGrades->get($key, collect());
                $tpList = $losSubjects->get($cs->id, collect());

                $tpGrades = $tpList->map(function ($los) use ($grades) {
                    $g = $grades->where('learning_objective_id', $los->learning_objective_id)->first();
                    return [
                        'tp_code' => $los->learningObjective->code,
                        'tp_description' => $los->learningObjective->description,
                        'nilai' => $g ? (float) $g->nilai : null,
                        'jenis_nilai' => $g?->jenis_nilai,
                    ];
                });

                $nilaiTp = $tpGrades->pluck('nilai')->filter(fn($n) => $n !== null);
                $rataRata = $nilaiTp->count() > 0 ? round($nilaiTp->avg(), 2) : null;

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
                    'class_subject_id' => $cs->id,
                    'subject_name' => $cs->subject->name,
                    'teacher_name' => $cs->teacher?->name,
                    'kkm' => $cs->kkm,
                    'rata_rata' => $rataRata,
                    'predikat' => $predikat,
                    'tp_grades' => $tpGrades,
                ];
            });

            // Presensi (rekap lengkap untuk rapor)
            $studentAtt = $attendances->get($student->id, collect());
            $totalAtt = $studentAtt->count();
            $hadirCount = $studentAtt->where('status', 'hadir')->count();
            $terlambatCount = $studentAtt->where('status', 'terlambat')->count();
            $sakitCount = $studentAtt->where('status', 'sakit')->count();
            $izinCount = $studentAtt->where('status', 'izin')->count();
            $alfaCount = $studentAtt->where('status', 'alfa')->count()
                + $studentAtt->where('status', 'tidak_hadir')->count();

            $attSummary = [
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'izin' => $izinCount,
                'sakit' => $sakitCount,
                'alfa' => $alfaCount,
                'total' => $totalAtt,
                'persentase_hadir' => $totalAtt > 0 ? round((($hadirCount + $terlambatCount) / $totalAtt) * 100, 1) : 0,
            ];

            // P5
            $studentP5 = collect($p5Assessments->get($student->id, []));
            $p5Data = $p5Projects->map(function ($project) use ($studentP5) {
                $ass = $studentP5->where('p5_project_id', $project->id)->first();
                return [
                    'project_id' => $project->id,
                    'tema' => $project->tema,
                    'judul' => $project->judul,
                    'dimensi' => $ass ? [
                        'dimensi_1' => $ass->dimensi_1,
                        'dimensi_2' => $ass->dimensi_2,
                        'dimensi_3' => $ass->dimensi_3,
                        'dimensi_4' => $ass->dimensi_4,
                        'dimensi_5' => $ass->dimensi_5,
                        'dimensi_6' => $ass->dimensi_6,
                    ] : null,
                    'catatan_proses' => $ass?->catatan_proses,
                ];
            });

            return [
                'student_id' => $student->id,
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'nisn' => $student->nisn ?? '',
                'kelas' => $student->class->code ?? '',
                'is_locked' => isset($lockedReports[$student->id]),
                'locked_at' => $lockedReports[$student->id]->locked_at ?? null,
                'subjects' => $subjects,
                'attendance' => $attSummary,
                'p5' => $p5Data,
            ];
        });

        return response()->json([
            'success' => true,
            'school' => [
                'name' => \App\Services\SchoolService::getValue('name', ''),
                'address' => \App\Services\SchoolService::getValue('address', ''),
                'npsn' => \App\Services\SchoolService::getValue('npsn', ''),
                'tempat_cetak' => \App\Services\SchoolService::getValue('tempat_cetak', ''),
                'kepala_sekolah' => \App\Services\SchoolService::getValue('principal_name', ''),
            ],
            'class' => [
                'name' => $homeroomClass->code,
                'tingkat' => $homeroomClass->tingkat,
                'wali_kelas' => $user->name,
            ],
            'semester' => [
                'id' => $semester->id,
                'label' => 'Semester ' . $semester->semester_number,
                'academic_year' => $semester->academicYear->year_label ?? '',
            ],
            'data' => $reportData,
        ]);
    }

    /**
     * Kunci rapor siswa (generate report dan set is_locked)
     */
    public function lock(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isWaliKelas()) {
            return response()->json(['success' => false, 'message' => 'Hanya wali kelas yang dapat mengunci rapor'], 403);
        }

        $validated = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $homeroomClass = SchoolClass::where('wali_kelas_id', $user->id)->first();
        if (!$homeroomClass) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki kelas wali'], 404);
        }

        DB::beginTransaction();
        try {
            $classSubjects = ClassSubject::where('class_id', $homeroomClass->id)->get();
            $locked = 0;

            foreach ($validated['student_ids'] as $studentId) {
                foreach ($classSubjects as $cs) {
                    $rataRata = Grade::where('student_id', $studentId)
                        ->where('class_subject_id', $cs->id)
                        ->where('semester_id', $validated['semester_id'])
                        ->avg('nilai');

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

                    Report::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'semester_id' => $validated['semester_id'],
                            'class_subject_id' => $cs->id,
                        ],
                        [
                            'nilai_akhir' => $rataRata ?? 0,
                            'predikat' => $predikat,
                            'deskripsi_cp' => null,
                            'is_locked' => true,
                            'locked_by' => $user->id,
                            'locked_at' => now(),
                        ]
                    );
                }
                $locked++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$locked rapor siswa berhasil dikunci",
                'locked_count' => $locked,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengunci rapor: ' . $e->getMessage()], 500);
        }
    }
}
