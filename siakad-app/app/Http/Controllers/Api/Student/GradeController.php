<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Semester;
use App\Models\ClassSubject;
use App\Models\LearningObjectiveSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Daftar nilai siswa per semester untuk semua mata pelajaran
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::with('class')->where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->value('id');
        if (!$semesterId) {
            return response()->json(['success' => false, 'message' => 'Tidak ada semester aktif'], 400);
        }

        $activeSemester = Semester::with('academicYear')->findOrFail($semesterId);

        $classSubjects = ClassSubject::with(['subject', 'teacher'])
            ->where('class_id', $student->class_id)
            ->get();

        $grades = Grade::with('learningObjective')
            ->where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->get()
            ->groupBy('class_subject_id');

        $subjects = $classSubjects->map(function ($cs) use ($grades) {
            $subjectGrades = $grades->get($cs->id, collect());
            $nilaiList = $subjectGrades->pluck('nilai')->filter(fn($n) => $n !== null);

            return [
                'class_subject_id' => $cs->id,
                'subject_name' => $cs->subject->name,
                'subject_code' => $cs->subject->code,
                'teacher_name' => $cs->teacher?->name,
                'kkm' => $cs->kkm,
                'nilai_rata_rata' => $nilaiList->count() > 0 ? round($nilaiList->avg(), 2) : null,
                'nilai_tertinggi' => $nilaiList->max(),
                'nilai_terendah' => $nilaiList->min(),
                'jumlah_tp' => $subjectGrades->groupBy('learning_objective_id')->count(),
                'details' => $subjectGrades->groupBy('learning_objective_id')->map(function ($tpGrades) {
                    $first = $tpGrades->first();
                    return [
                        'tp_code' => $first->learningObjective?->code,
                        'tp_description' => $first->learningObjective?->description,
                        'nilai' => $tpGrades->map(fn($g) => [
                            'nilai' => (float) $g->nilai,
                            'jenis' => $g->jenis_nilai,
                            'tanggal' => $g->created_at?->format('Y-m-d'),
                        ])->values(),
                        'rata_rata' => round($tpGrades->avg('nilai'), 2),
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'nama' => $student->nama_lengkap,
                'kelas' => $student->class->code ?? '',
            ],
            'semester' => [
                'id' => $activeSemester->id,
                'label' => 'Semester ' . $activeSemester->semester_number,
                'tahun_ajaran' => $activeSemester->academicYear->year_label ?? '',
            ],
            'data' => $subjects,
        ]);
    }

    /**
     * Detail nilai per mata pelajaran dengan TP breakdown
     */
    public function show(Request $request, string $grade): JsonResponse
    {
        $user = $request->user();
        $student = Student::with('class')->where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $classSubject = ClassSubject::with(['subject', 'teacher', 'schoolClass'])
            ->findOrFail($grade);

        $semesterId = $request->semester_id ?? Semester::where('is_active', true)->value('id');

        $grades = Grade::with('learningObjective')
            ->where('student_id', $student->id)
            ->where('class_subject_id', $classSubject->id)
            ->where('semester_id', $semesterId)
            ->get();

        $losSubjects = LearningObjectiveSubject::with('learningObjective')
            ->where('class_subject_id', $classSubject->id)
            ->where('semester_id', $semesterId)
            ->orderBy('urutan_ajar')
            ->get();

        $tpGrades = $losSubjects->map(function ($los) use ($grades) {
            $tpGrade = $grades->where('learning_objective_id', $los->learning_objective_id);
            return [
                'tp_id' => $los->learningObject_id,
                'tp_code' => $los->learningObjective->code,
                'tp_description' => $los->learningObjective->description,
                'urutan' => $los->urutan_ajar,
                'nilai' => $tpGrade->map(fn($g) => [
                    'nilai' => (float) $g->nilai,
                    'jenis_nilai' => $g->jenis_nilai,
                    'deskripsi' => $g->deskripsi,
                ])->values(),
                'rata_rata' => $tpGrade->count() > 0 ? round($tpGrade->avg('nilai'), 2) : null,
            ];
        });

        $allNilai = $grades->pluck('nilai')->filter(fn($n) => $n !== null);
        $rataRata = $allNilai->count() > 0 ? round($allNilai->avg(), 2) : null;

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

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => $classSubject->subject->name,
                'teacher' => $classSubject->teacher?->name,
                'kkm' => $classSubject->kkm,
                'nilai_rata_rata' => $rataRata,
                'predikat' => $predikat,
                'jumlah_tp' => $losSubjects->count(),
                'tp_grades' => $tpGrades,
            ],
        ]);
    }
}
