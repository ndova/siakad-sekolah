<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\LearningObjectiveSubject;
use App\Models\Student;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    /**
     * Daftar mapel yang diampu guru saat ini
     */
    public function classSubjects(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $activeSemester = Semester::whereHas('academicYear', fn($q) => $q->where('school_id', $schoolId)->where('is_active', true))
            ->where('is_active', true)
            ->first();

        $classSubjects = ClassSubject::with(['subject', 'schoolClass'])
            ->where('teacher_id', $user->id)
            ->whereHas('schoolClass', fn($q) => $q->where('school_id', $schoolId)->where('is_active', true))
            ->get()
            ->map(function ($cs) {
                return [
                    'id' => $cs->id,
                    'class_id' => $cs->class_id,
                    'class_name' => $cs->schoolClass->code . ' - ' . $cs->schoolClass->tingkat,
                    'subject_id' => $cs->subject_id,
                    'subject_name' => $cs->subject->name,
                    'subject_code' => $cs->subject->code,
                    'kkm' => $cs->kkm,
                    'student_count' => Student::where('class_id', $cs->class_id)->where('status', 'aktif')->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'semester_id' => $activeSemester?->id,
            'data' => $classSubjects,
        ]);
    }

    /**
     * Daftar nilai per class_subject & learning_objective
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subject,id',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        $classSubject = ClassSubject::with(['subject', 'schoolClass'])->findOrFail($validated['class_subject_id']);

        $semesterId = $validated['semester_id'] ?? Semester::whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->where('is_active', true)->value('id');

        $students = Student::with(['user'])
            ->where('class_id', $classSubject->class_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $losSubjects = LearningObjectiveSubject::with(['learningObjective'])
            ->where('class_subject_id', $classSubject->id)
            ->where('semester_id', $semesterId)
            ->orderBy('urutan_ajar')
            ->get();

        $tpList = $losSubjects->map(function ($los) {
            return [
                'id' => $los->learningObjective->id,
                'code' => $los->learningObjective->code,
                'description' => $los->learningObjective->description,
                'urutan' => $los->urutan_ajar,
            ];
        });

        $existingGrades = Grade::where('class_subject_id', $classSubject->id)
            ->where('semester_id', $semesterId)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereIn('learning_objective_id', $tpList->pluck('id'))
            ->get()
            ->groupBy(function ($g) {
                return $g->student_id . '_' . $g->learning_objective_id;
            });

        $studentGrades = $students->map(function ($student) use ($tpList, $existingGrades) {
            $grades = [];
            $total = 0;
            $count = 0;
            foreach ($tpList as $tp) {
                $key = $student->id . '_' . $tp['id'];
                $grade = $existingGrades->get($key)?->first();
                $nilai = $grade ? (float) $grade->nilai : null;
                $grades[] = [
                    'tp_id' => $tp['id'],
                    'tp_code' => $tp['code'],
                    'nilai' => $nilai,
                    'jenis_nilai' => $grade?->jenis_nilai,
                    'deskripsi' => $grade?->deskripsi,
                ];
                if ($nilai !== null) {
                    $total += $nilai;
                    $count++;
                }
            }
            return [
                'student_id' => $student->id,
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'grades' => $grades,
                'rata_rata' => $count > 0 ? round($total / $count, 2) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'class_subject' => [
                'id' => $classSubject->id,
                'subject' => $classSubject->subject->name,
                'class' => $classSubject->schoolClass->code,
                'kkm' => $classSubject->kkm,
            ],
            'tp_list' => $tpList,
            'students' => $studentGrades,
        ]);
    }

    /**
     * Simpan/batch simpan nilai
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subject,id',
            'semester_id' => 'required|exists:semesters,id',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.learning_objective_id' => 'required|exists:learning_objectives,id',
            'grades.*.nilai' => 'required|numeric|min:0|max:100',
            'grades.*.jenis_nilai' => ['nullable', Rule::in(['formatif', 'sumatif', 'pts', 'pas', 'sas'])],
            'grades.*.deskripsi' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $saved = 0;
            foreach ($validated['grades'] as $item) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'class_subject_id' => $validated['class_subject_id'],
                        'learning_objective_id' => $item['learning_objective_id'],
                        'semester_id' => $validated['semester_id'],
                    ],
                    [
                        'nilai' => $item['nilai'],
                        'jenis_nilai' => $item['jenis_nilai'] ?? 'formatif',
                        'deskripsi' => $item['deskripsi'] ?? null,
                        'created_by' => $user->id,
                    ]
                );
                $saved++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$saved nilai berhasil disimpan",
                'saved_count' => $saved,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update satu nilai
     */
    public function update(Request $request, Grade $grade): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'jenis_nilai' => ['nullable', Rule::in(['formatif', 'sumatif', 'pts', 'pas', 'sas'])],
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $grade->update([
            'nilai' => $validated['nilai'],
            'jenis_nilai' => $validated['jenis_nilai'] ?? $grade->jenis_nilai,
            'deskripsi' => $validated['deskripsi'] ?? $grade->deskripsi,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil diperbarui',
            'data' => $grade->fresh()->load('student', 'learningObjective'),
        ]);
    }

    /**
     * Hapus nilai
     */
    public function destroy(Request $request, Grade $grade): JsonResponse
    {
        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil dihapus',
        ]);
    }
}
