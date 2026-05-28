<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\ExamAnswer;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Daftar ujian yang dibuat guru ini
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $exams = Exam::with(['subject:id,name,code', 'semester:id,semester_number,is_active'])
            ->where('created_by', $user->id)
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('title', 'like', "%{$request->search}%")
                        ->orWhere('code', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $exams->through(fn($e) => [
                'id' => $e->id,
                'code' => $e->code,
                'title' => $e->title,
                'type' => $e->type,
                'subject' => $e->subject?->name,
                'semester' => $e->semester?->semester_number,
                'total_questions' => $e->total_questions,
                'total_score' => $e->total_score,
                'minimum_score' => $e->minimum_score,
                'duration' => $e->duration,
                'start_time' => $e->start_time?->toISOString(),
                'end_time' => $e->end_time?->toISOString(),
                'status' => $e->status,
                'class_ids' => $e->class_ids,
                'class_codes' => $e->class_codes,
                'created_at' => $e->created_at?->toISOString(),
            ]),
            'meta' => [
                'current_page' => $exams->currentPage(),
                'last_page' => $exams->lastPage(),
                'total' => $exams->total(),
            ],
        ]);
    }

    /**
     * Buat ujian baru
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => ['required', 'in:uh,sts,sas,asaj,tryout,remedi'],
            'subject_id' => 'required|exists:subjects,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'semester_id' => 'required|exists:semesters,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'random_questions' => 'boolean',
            'random_answers' => 'boolean',
            'show_result' => 'boolean',
            'max_devices' => 'nullable|integer|min:1|max:10',
            'minimum_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $activeSemester = Semester::where('is_active', true)->first();
        $semesterId = $validated['semester_id'] ?? $activeSemester?->id;

        $exam = Exam::create([
            'school_id' => $user->school_id,
            'code' => 'EX-' . strtoupper(uniqid()),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'subject_id' => $validated['subject_id'],
            'class_ids' => $validated['class_ids'],
            'semester_id' => $semesterId,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration' => $validated['duration'],
            'total_questions' => 0,
            'total_score' => 0,
            'random_questions' => $validated['random_questions'] ?? false,
            'random_answers' => $validated['random_answers'] ?? false,
            'show_result' => $validated['show_result'] ?? false,
            'max_devices' => $validated['max_devices'] ?? 1,
            'minimum_score' => $validated['minimum_score'] ?? null,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dibuat',
            'data' => $exam->fresh()->load('subject', 'semester'),
        ], 201);
    }

    /**
     * Detail ujian beserta soal-soalnya
     */
    public function show(Request $request, Exam $exam): JsonResponse
    {
        $exam->load([
            'subject', 'semester', 'creator:id,name',
            'examQuestions' => function ($q) {
                $q->orderBy('urutan')->with(['question' => function ($q) {
                    $q->with('learningObjective:id,code,description');
                }]);
            },
        ]);

        $questions = $exam->examQuestions->map(function ($eq) {
            $q = $eq->question;
            return [
                'id' => $eq->id,
                'question_id' => $q->id,
                'urutan' => $eq->urutan,
                'score_override' => $eq->score_override,
                'type' => $q->type,
                'content' => $q->content,
                'options' => $q->type === 'pg' ? $q->options : null,
                'answer_key' => $q->answer_key,
                'score' => $q->score,
                'level_kognitif' => $q->level_kognitif,
                'difficulty' => $q->difficulty,
                'tp_code' => $q->learningObjective?->code,
                'tp_description' => $q->learningObjective?->description,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $exam->id,
                'code' => $exam->code,
                'title' => $exam->title,
                'type' => $exam->type,
                'subject' => $exam->subject?->name,
                'semester' => $exam->semester?->semester_number,
                'class_ids' => $exam->class_ids,
                'class_codes' => $exam->class_codes,
                'start_time' => $exam->start_time?->toISOString(),
                'end_time' => $exam->end_time?->toISOString(),
                'duration' => $exam->duration,
                'total_questions' => $exam->total_questions,
                'total_score' => $exam->total_score,
                'minimum_score' => $exam->minimum_score,
                'status' => $exam->status,
                'random_questions' => $exam->random_questions,
                'random_answers' => $exam->random_answers,
                'show_result' => $exam->show_result,
                'creator' => $exam->creator?->name,
                'questions' => $questions,
            ],
        ]);
    }

    /**
     * Update ujian
     */
    public function update(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => ['required', 'in:uh,sts,sas,asaj,tryout,remedi'],
            'subject_id' => 'required|exists:subjects,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'semester_id' => 'required|exists:semesters,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'status' => ['in:draft,published,ongoing,finished'],
            'random_questions' => 'boolean',
            'random_answers' => 'boolean',
            'show_result' => 'boolean',
            'max_devices' => 'nullable|integer|min:1|max:10',
            'minimum_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $exam->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil diperbarui',
            'data' => $exam->fresh()->load('subject', 'semester'),
        ]);
    }

    /**
     * Tambah soal ke ujian dari bank soal
     */
    public function addQuestions(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question_id' => 'required|exists:questions,id',
            'questions.*.score_override' => 'nullable|numeric|min:0',
            'question_bank_id' => 'nullable|exists:question_banks,id',
            'random_count' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $questionData = $validated['questions'];

            // Jika random_count disediakan, ambil soal acak dari bank
            if (!empty($validated['random_count']) && !empty($validated['question_bank_id'])) {
                $randomQuestions = Question::where('question_bank_id', $validated['question_bank_id'])
                    ->inRandomOrder()
                    ->take($validated['random_count'])
                    ->get();

                $questionData = $randomQuestions->map(fn($q) => ['question_id' => $q->id, 'score_override' => null])->toArray();
            }

            $existingCount = $exam->examQuestions()->count();
            $totalAdded = 0;
            $totalScore = $exam->total_score;

            foreach ($questionData as $i => $item) {
                $question = Question::findOrFail($item['question_id']);
                $score = $item['score_override'] ?? $question->score;

                $exam->examQuestions()->create([
                    'question_id' => $item['question_id'],
                    'urutan' => $existingCount + $i + 1,
                    'score_override' => $item['score_override'] ?? null,
                ]);

                $totalScore += $score;
                $totalAdded++;
            }

            // Update total questions & score
            $exam->update([
                'total_questions' => $exam->examQuestions()->count(),
                'total_score' => $totalScore,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$totalAdded soal berhasil ditambahkan",
                'total_questions' => $exam->total_questions,
                'total_score' => $exam->total_score,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menambah soal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hasil ujian
     */
    public function results(Request $request, Exam $exam): JsonResponse
    {
        $results = ExamResult::with(['student:id,nama_lengkap,nis,class_id', 'student.class:id,code,tingkat'])
            ->where('exam_id', $exam->id)
            ->orderBy('total_score', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'student' => [
                        'id' => $r->student->id,
                        'nama' => $r->student->nama_lengkap,
                        'nis' => $r->student->nis,
                        'kelas' => $r->student->class?->code,
                    ],
                    'total_score' => $r->total_score,
                    'correct_count' => $r->correct_count,
                    'wrong_count' => $r->wrong_count,
                    'total_questions' => $r->correct_count + $r->wrong_count,
                    'tp_scores' => $r->tp_scores,
                    'is_passed' => $r->is_passed,
                    'graded_at' => $r->graded_at?->toISOString(),
                ];
            });

        $stats = [
            'total_peserta' => $results->count(),
            'rata_rata' => $results->count() > 0 ? round($results->avg('total_score'), 2) : 0,
            'nilai_tertinggi' => $results->max('total_score') ?? 0,
            'nilai_terendah' => $results->min('total_score') ?? 0,
            'lulus' => $results->where('is_passed', true)->count(),
            'tidak_lulus' => $results->where('is_passed', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'total_score' => $exam->total_score,
                'minimum_score' => $exam->minimum_score,
            ],
            'stats' => $stats,
            'data' => $results,
        ]);
    }

    /**
     * Koreksi hasil ujian (essay / override score)
     */
    public function grade(Request $request, ExamResult $result): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'total_score' => 'nullable|numeric|min:0',
            'tp_scores' => 'nullable|array',
            'is_passed' => 'boolean',
        ]);

        $updateData = [
            'graded_by' => $user->id,
            'graded_at' => now(),
        ];

        if (isset($validated['total_score'])) {
            $updateData['total_score'] = $validated['total_score'];
        }
        if (isset($validated['tp_scores'])) {
            $updateData['tp_scores'] = $validated['tp_scores'];
        }
        if (isset($validated['is_passed'])) {
            $updateData['is_passed'] = $validated['is_passed'];
        }

        $result->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Hasil ujian berhasil dikoreksi',
            'data' => $result->fresh(),
        ]);
    }

    /**
     * Hapus hasil ujian beserta sesi dan jawaban.
     */
    public function destroyResult(Request $request, ExamResult $result): JsonResponse
    {
        // Hapus sesi ujian (cascade delete answers)
        if ($result->examSession) {
            $result->examSession->answers()->delete();
            $result->examSession->delete();
        }

        $result->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hasil ujian berhasil dihapus.',
        ]);
    }
}
