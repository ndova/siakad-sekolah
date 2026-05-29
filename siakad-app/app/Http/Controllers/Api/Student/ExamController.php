<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Mulai sesi ujian.
     */
    public function startExam(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        // Cek apakah ujian tersedia untuk kelas siswa ini
        if (!in_array($student->class_id, $exam->class_ids ?? [])) {
            return response()->json(['success' => false, 'message' => 'Ujian tidak tersedia untuk kelas Anda'], 403);
        }

        // Cek status ujian
        if (!in_array($exam->status, ['published', 'ongoing'])) {
            return response()->json(['success' => false, 'message' => 'Ujian belum dibuka atau sudah selesai'], 400);
        }

        // Cek waktu
        $now = now();
        if ($exam->start_time && $now->lt($exam->start_time)) {
            return response()->json(['success' => false, 'message' => 'Ujian belum dimulai'], 400);
        }
        if ($exam->end_time && $now->gt($exam->end_time)) {
            return response()->json(['success' => false, 'message' => 'Waktu ujian sudah berakhir'], 400);
        }

        // Cek sesi yang sudah ada
        $existingSession = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSession && $existingSession->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Anda sudah menyelesaikan ujian ini'], 400);
        }

        DB::beginTransaction();
        try {
            if ($existingSession) {
                // Lanjutkan sesi yang sudah ada
                $session = $existingSession;
                if ($session->status !== 'in_progress') {
                    $session->update(['status' => 'in_progress']);
                }
            } else {
                // Buat sesi baru
                $session = ExamSession::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'started_at' => $now,
                    'status' => 'in_progress',
                    'ip_address' => $request->ip(),
                    'device_info' => $request->header('User-Agent'),
                ]);
            }

            // Update status ujian menjadi ongoing jika masih published
            if ($exam->status === 'published') {
                $exam->update(['status' => 'ongoing']);
            }

            // Simpan remaining_seconds dari durasi
            if (!$session->remaining_seconds) {
                $remainingSeconds = $exam->duration * 60;
                // Jika sesi sudah berjalan, kurangi waktu yang sudah berlalu
                if ($session->started_at) {
                    $elapsed = (int) $now->diffInSeconds($session->started_at, true);
                    $remainingSeconds = max(0, $remainingSeconds - $elapsed);
                }
                $session->update(['remaining_seconds' => $remainingSeconds]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $existingSession ? 'Melanjutkan sesi ujian' : 'Sesi ujian dimulai',
                'data' => [
                    'session_id' => $session->id,
                    'remaining_seconds' => (int) $session->remaining_seconds,
                    'started_at' => $session->started_at?->toISOString(),
                    'status' => $session->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai ujian: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil soal ujian (TANPA answer_key).
     */
    public function getQuestions(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        // Cari sesi aktif
        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Anda belum memulai ujian ini'], 400);
        }

        // Update remaining_seconds berdasarkan waktu aktual
        $elapsed = $session->started_at ? (int) now()->diffInSeconds($session->started_at, true) : 0;
        $totalSeconds = $exam->duration * 60;
        $remainingSeconds = max(0, $totalSeconds - $elapsed);
        $session->update(['remaining_seconds' => $remainingSeconds]);

        // Cek waktu habis
        if ($remainingSeconds <= 0) {
            return response()->json(['success' => false, 'message' => 'Waktu ujian telah habis'], 400);
        }

        // Ambil soal
        $exam->load([
            'subject:id,name,code',
            'examQuestions' => function ($q) {
                $q->orderBy('urutan')->with(['question' => function ($q) {
                    $q->select('id', 'type', 'content', 'options', 'score', 'level_kognitif', 'difficulty', 'question_bank_id');
                }]);
            },
        ]);

        // Ambil jawaban yang sudah ada untuk sesi ini
        $existingAnswers = ExamAnswer::where('exam_session_id', $session->id)
            ->get()
            ->keyBy('exam_question_id');

        $questions = $exam->examQuestions->map(function ($eq) use ($existingAnswers) {
            $q = $eq->question;
            $answer = $existingAnswers->get($eq->id);

            $questionData = [
                'id' => $eq->id,
                'urutan' => $eq->urutan,
                'type' => $q->type,
                'content' => $q->content,
                'options' => $q->options,
                'score' => $eq->score_override ?? $q->score,
                'level_kognitif' => $q->level_kognitif,
                'difficulty' => $q->difficulty,
                // Jawaban yang sudah disimpan
                'my_answer' => [
                    'selected_options' => $answer?->selected_options,
                    'text_answer' => $answer?->text_answer,
                ],
                'is_answered' => $answer !== null,
            ];

            return $questionData;
        });

        // Hitung sisa waktu
        $endTime = null;
        if ($session->started_at) {
            $endTime = $session->started_at->copy()->addMinutes($exam->duration);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'type' => $exam->type,
                    'subject' => $exam->subject?->name,
                    'duration' => $exam->duration,
                    'total_questions' => $exam->total_questions,
                    'total_score' => $exam->total_score,
                    'start_time' => $exam->start_time?->toISOString(),
                    'end_time' => $exam->end_time?->toISOString(),
                    'show_result' => $exam->show_result,
                ],
                'session' => [
                    'id' => $session->id,
                    'started_at' => $session->started_at?->toISOString(),
                    'end_time' => $endTime?->toISOString(),
                    'remaining_seconds' => $remainingSeconds,
                    'status' => $session->status,
                ],
                'questions' => $questions,
                'answered_count' => $existingAnswers->count(),
            ],
        ]);
    }

    /**
     * Simpan jawaban untuk satu soal. Auto-grade PG & BS.
     */
    public function submitAnswer(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi ujian tidak aktif'], 400);
        }

        // Cek waktu
        $elapsed = $session->started_at ? (int) now()->diffInSeconds($session->started_at, true) : 0;
        if ($elapsed > ($exam->duration * 60)) {
            return response()->json(['success' => false, 'message' => 'Waktu ujian telah habis'], 400);
        }

        $validated = $request->validate([
            'exam_question_id' => 'required|exists:exam_questions,id',
            'selected_options' => 'nullable|array',
            'text_answer' => 'nullable|string',
        ]);

        // Verifikasi soal milik ujian ini
        $examQuestion = ExamQuestion::where('id', $validated['exam_question_id'])
            ->where('exam_id', $exam->id)
            ->with('question')
            ->first();

        if (!$examQuestion) {
            return response()->json(['success' => false, 'message' => 'Soal tidak ditemukan dalam ujian ini'], 400);
        }

        $question = $examQuestion->question;
        $isCorrect = null;
        $score = null;

        // Auto-grade untuk PG, BS, Audio, dan Jodoh
        if (in_array($question->type, ['pg', 'bs', 'audio'])) {
            $selected = $validated['selected_options'] ?? [];
            $answerKey = $question->answer_key;

            // Normalisasi untuk perbandingan case-insensitive
            $selectedVal = !empty($selected) ? strtolower($selected[0]) : null;
            $correctVal = strtolower($answerKey ?? '');

            $isCorrect = $selectedVal !== null && $selectedVal === $correctVal;

            $maxScore = $examQuestion->score_override ?? $question->score ?? 10;
            $score = $isCorrect ? $maxScore : 0;
        } elseif ($question->type === 'jodoh') {
            // Jodoh: options = {kiri1:kanan1, kiri2:kanan2, ...}
            // Jawaban: selected_options = ["kanan1", "kanan2", ...] (teks jawaban yang dipilih)
            // Skor: (jumlah benar / total pasangan) × skor_maksimal
            $selected = $validated['selected_options'] ?? [];
            $options = $question->options ?? [];
            $correctValues = array_values($options); // Jawaban benar sesuai urutan kiri
            $totalPairs = count($correctValues);

            $correctCount = 0;
            foreach ($correctValues as $i => $correctVal) {
                $studentVal = $selected[$i] ?? null;
                if ($studentVal !== null && strtolower($studentVal) === strtolower($correctVal ?? '')) {
                    $correctCount++;
                }
            }

            $isCorrect = $totalPairs > 0 && $correctCount === $totalPairs;
            $maxScore = $examQuestion->score_override ?? $question->score ?? 10;
            $score = $totalPairs > 0 ? round(($correctCount / $totalPairs) * $maxScore, 1) : 0;
        }

        // Upsert answer
        ExamAnswer::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'exam_question_id' => $examQuestion->id,
            ],
            [
                'selected_options' => $validated['selected_options'] ?? null,
                'text_answer' => $validated['text_answer'] ?? null,
                'is_correct' => $isCorrect,
                'score' => $score,
            ]
        );

        // Update remaining_seconds
        $totalSeconds = $exam->duration * 60;
        $remainingSeconds = max(0, $totalSeconds - $elapsed);
        $session->update(['remaining_seconds' => $remainingSeconds]);

        // Hitung jumlah terjawab
        $answeredCount = ExamAnswer::where('exam_session_id', $session->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Jawaban disimpan',
            'data' => [
                'answered_count' => $answeredCount,
                'total_questions' => $exam->total_questions,
                'remaining_seconds' => $remainingSeconds,
                'is_correct' => $isCorrect,
                'score' => $score,
            ],
        ]);
    }

    /**
     * Akhiri ujian dan hitung hasil.
     */
    public function finishExam(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi ujian tidak aktif'], 400);
        }

        DB::beginTransaction();
        try {
            // Hitung elapsed time dari server
            $elapsed = $session->started_at ? (int) now()->diffInSeconds($session->started_at, true) : 0;
            $durationSeconds = $exam->duration * 60;
            $finishedAt = $elapsed >= $durationSeconds
                ? $session->started_at->copy()->addSeconds($durationSeconds)
                : now();

            // Tandai sesi selesai
            $session->update([
                'finished_at' => $finishedAt,
                'status' => 'finished',
                'remaining_seconds' => 0,
            ]);

            // Hitung hasil — re-grade jodoh/audio yang belum dinilai
            $answers = ExamAnswer::where('exam_session_id', $session->id)
                ->with('examQuestion.question')
                ->get();

            // Re-grade jawaban jodoh/audio yang belum dinilai atau dalam format label lama
            foreach ($answers as $answer) {
                $q = $answer->examQuestion->question ?? null;
                if (!$q) continue;

                // Deteksi jawaban jodoh format label lama (A, B, C...) — reset ke null
                if ($q->type === 'jodoh' && !empty($answer->selected_options)) {
                    $isLabelFormat = true;
                    foreach ($answer->selected_options as $v) {
                        if (!is_string($v) || strlen($v) !== 1 || !ctype_upper($v)) {
                            $isLabelFormat = false;
                            break;
                        }
                    }
                    if ($isLabelFormat) {
                        $answer->update(['is_correct' => null, 'score' => null]);
                        continue;
                    }
                }

                if ($answer->is_correct !== null) continue;

                if ($q->type === 'jodoh') {
                    $selected = $answer->selected_options ?? [];
                    $correctValues = array_values($q->options ?? []);
                    $totalPairs = count($correctValues);
                    $correctCount = 0;
                    foreach ($correctValues as $i => $correctVal) {
                        $studentVal = $selected[$i] ?? null;
                        if ($studentVal !== null && strtolower($studentVal) === strtolower($correctVal ?? '')) {
                            $correctCount++;
                        }
                    }
                    $maxScore = $answer->examQuestion->score_override ?? $q->score ?? 10;
                    $score = $totalPairs > 0 ? round(($correctCount / $totalPairs) * $maxScore, 1) : 0;
                    $answer->update([
                        'is_correct' => $totalPairs > 0 && $correctCount === $totalPairs,
                        'score' => $score,
                    ]);
                } elseif (in_array($q->type, ['audio'])) {
                    $selected = $answer->selected_options ?? [];
                    $selectedVal = !empty($selected) ? strtolower($selected[0]) : null;
                    $correctVal = strtolower($q->answer_key ?? '');
                    $isCorrect = $selectedVal !== null && $selectedVal === $correctVal;
                    $maxScore = $answer->examQuestion->score_override ?? $q->score ?? 10;
                    $answer->update([
                        'is_correct' => $isCorrect,
                        'score' => $isCorrect ? $maxScore : 0,
                    ]);
                }
            }

            // Refresh answers setelah re-grade
            $answers = ExamAnswer::where('exam_session_id', $session->id)->get();
            $correctCount = $answers->where('is_correct', true)->count();
            $wrongCount = $answers->where('is_correct', false)->count();
            $totalScore = $answers->sum('score') ?? 0;

            // Hitung skor maksimal riil (jumlah semua soal × bobot masing-masing)
            $maxPossibleScore = ExamQuestion::where('exam_id', $exam->id)
                ->with('question')
                ->get()
                ->sum(fn($eq) => $eq->score_override ?? $eq->question->score ?? 10);

            // KKM: gunakan minimum_score jika diset, atau 70 sebagai default
            $kkmScore = $exam->minimum_score ?? 70;

            // Nilai akhir = persentase dari skor maksimal × 100
            $finalScore = $maxPossibleScore > 0
                ? round(($totalScore / $maxPossibleScore) * 100, 1)
                : 0;
            $isPassed = $finalScore >= $kkmScore;

            // Buat / update ExamResult
            $result = ExamResult::updateOrCreate(
                [
                    'exam_session_id' => $session->id,
                ],
                [
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'total_score' => $finalScore,
                    'correct_count' => $correctCount,
                    'wrong_count' => $wrongCount,
                    'is_passed' => $isPassed,
                ]
            );

            // Cek apakah semua siswa sudah selesai
            $remainingSessions = ExamSession::where('exam_id', $exam->id)
                ->where('status', 'in_progress')
                ->count();

            if ($remainingSessions === 0) {
                $exam->update(['status' => 'finished']);
            }

            // Cek apakah ujian memiliki soal yang butuh koreksi manual (esai, jodoh, audio)
            $examQuestionTypes = ExamQuestion::where('exam_id', $exam->id)
                ->with('question')
                ->get()
                ->pluck('question.type')
                ->unique()
                ->toArray();

            $needsManualGrading = !empty(array_intersect($examQuestionTypes, ['esai']));

            // Jika butuh koreksi manual, jangan tampilkan skor dulu
            $showResult = $exam->show_result && !$needsManualGrading;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ujian selesai. ' . ($needsManualGrading ? 'Hasil akan diumumkan setelah dikoreksi guru.' : ''),
                'data' => [
                    'total_score' => $showResult ? $finalScore : null,
                    'correct_count' => $showResult ? $correctCount : null,
                    'wrong_count' => $showResult ? $wrongCount : null,
                    'total_questions' => $exam->total_questions,
                    'is_passed' => $showResult ? $isPassed : null,
                    'minimum_score' => $kkmScore,
                    'show_result' => $showResult,
                    'needs_grading' => $needsManualGrading,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengakhiri ujian: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Simpan remaining_seconds (sync timer).
     */
    public function saveTime(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi ujian tidak aktif'], 400);
        }

        $validated = $request->validate([
            'remaining_seconds' => 'required|integer|min:0',
        ]);

        // Hitung remaining berdasarkan server time sebagai validasi silang
        $elapsed = $session->started_at ? (int) now()->diffInSeconds($session->started_at, true) : 0;
        $serverRemaining = max(0, ($exam->duration * 60) - $elapsed);

        // Gunakan nilai terkecil (client atau server) untuk mencegah manipulasi
        $safeRemaining = min((int) $validated['remaining_seconds'], $serverRemaining);

        $session->update(['remaining_seconds' => $safeRemaining]);

        $endTime = $session->started_at
            ? $session->started_at->copy()->addSeconds($exam->duration * 60)
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'remaining_seconds' => $safeRemaining,
                'end_time' => $endTime?->toISOString(),
            ],
        ]);
    }
}
