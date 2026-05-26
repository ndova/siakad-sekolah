<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    protected function schoolId() { return auth()->user()->school_id; }
    protected function activeSemesterId() { return Semester::whereHas('academicYear', fn($q)=>$q->where('school_id',$this->schoolId()))->where('is_active',true)->value('id'); }

    // ─── QUESTION BANKS ─────────────────────────────────────────────────
    public function banks(Request $request)
    {
        $user = auth()->user();
        $query = QuestionBank::with(['subject','creator','schoolClass.major'])
            ->withCount('questions')
            ->where('school_id', $this->schoolId());

        // Guru hanya lihat bank soal untuk mapel yang diampu
        if ($user->role === 'guru') {
            $taughtSubjectIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $query->whereIn('subject_id', $taughtSubjectIds);
        }

        $banks = $query->when($request->class_id, fn($q,$c)=>$q->where('class_id',$c))
            ->when($request->subject_id, fn($q,$s)=>$q->where('subject_id',$s))
            ->orderBy('name')->paginate(15)->withQueryString();

        // Daftar kelas untuk filter (beserta jurusan)
        $classQuery = SchoolClass::with('major')
            ->where('school_id', $this->schoolId())
            ->where('is_active', true);
        if ($user->role === 'guru') {
            $taughtClassIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $classQuery->whereIn('id', $taughtClassIds);
        }
        $classes = $classQuery->orderBy('tingkat')->orderBy('code')->get();

        // Mata pelajaran untuk filter
        $subjectQuery = Subject::where('school_id', $this->schoolId())->where('is_active',true);
        if ($user->role === 'guru') {
            $taughtIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $subjectQuery->whereIn('id', $taughtIds);
        }

        // Filter mapel berdasarkan kelas yang dipilih
        if ($request->class_id) {
            $subjectIdsForClass = \App\Models\ClassSubject::where('class_id', $request->class_id)
                ->pluck('subject_id')->unique();
            $subjectQuery->whereIn('id', $subjectIdsForClass);
        }
        $subjects = $subjectQuery->orderBy('code')->get();

        // Data class_subject untuk mapping (kelas → list mapel)
        $classSubjects = \App\Models\ClassSubject::with('subject')
            ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId())->where('is_active', true))
            ->get()
            ->groupBy('class_id')
            ->map(fn($items) => $items->map(fn($cs) => [
                'subject_id' => $cs->subject_id,
                'code' => $cs->subject->code ?? '',
                'name' => $cs->subject->name ?? '',
            ])->values());

        return view('backend.exam.banks', compact('banks','classes','subjects','classSubjects'));
    }

    public function storeBank(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'is_shared' => 'boolean',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['created_by'] = auth()->id();
        QuestionBank::create($data);
        return back()->with('success','Bank soal dibuat.');
    }

    // ─── QUESTIONS ──────────────────────────────────────────────────────
    public function questions(Request $request)
    {
        $user = auth()->user();
        $query = Question::with(['questionBank.subject','learningObjective','creator']);

        // Guru hanya lihat soal di bank soal mapel yang diampu
        if ($user->role === 'guru') {
            $taughtSubjectIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $query->whereHas('questionBank', fn($q) => $q->whereIn('subject_id', $taughtSubjectIds));
        }

        $allQuestions = $query->when($request->question_bank_id, fn($q,$b)=>$q->where('question_bank_id',$b))
            ->when($request->type, fn($q,$t)=>$q->where('type',$t))
            ->when($request->search, fn($q,$s)=>$q->where('content','like',"%$s%"))
            ->orderBy('created_at','desc')->get();

        // Kelompokkan soal per mata pelajaran
        $groupedQuestions = $allQuestions->groupBy(fn($q) => $q->questionBank->subject_id ?? 'unknown')
            ->map(fn($questions) => [
                'subject' => $questions->first()->questionBank->subject,
                'questions' => $questions,
                'count' => $questions->count(),
                'pg' => $questions->where('type','pg')->count(),
                'esai' => $questions->where('type','esai')->count(),
            ])->sortBy(fn($g) => $g['subject']->code ?? 'Z');

        $totalQuestions = $allQuestions->count();

        $bankQuery = QuestionBank::with('subject')->where('school_id', $this->schoolId());
        if ($user->role === 'guru') {
            $taughtIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $bankQuery->whereIn('subject_id', $taughtIds);
        }
        $banks = $bankQuery->orderBy('name')->get();
        $learningObjectives = \App\Models\LearningObjective::with('learningOutcome.subject')->get();

        // Data soal untuk JavaScript modal edit (semua soal, tidak hanya halaman tertentu)
        $questionData = $allQuestions->keyBy('id')->map(function($q) {
            return [
                'question_bank_id' => $q->question_bank_id,
                'learning_objective_id' => $q->learning_objective_id,
                'type' => $q->type,
                'content' => $q->content,
                'options' => $q->options,
                'answer_key' => $q->answer_key,
                'score' => $q->score,
                'level_kognitif' => $q->level_kognitif,
                'difficulty' => $q->difficulty,
                'has_audio' => !empty($q->media['audio']),
                'audio_url' => !empty($q->media['audio']) ? asset('storage/'.$q->media['audio']) : null,
            ];
        });

        return view('backend.exam.questions', compact('groupedQuestions','totalQuestions','banks','learningObjectives','questionData'));
    }

    public function storeQuestion(Request $request)
    {
        $data = $request->validate([
            'question_bank_id' => 'required|exists:question_banks,id',
            'learning_objective_id' => 'nullable|exists:learning_objectives,id',
            'type' => 'required|in:pg,bs,jodoh,esai,audio',
            'content' => 'required|string',
            'options' => 'nullable|json',
            'answer_key' => 'nullable|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'level_kognitif' => 'nullable|in:L1,L2,L3',
            'difficulty' => 'nullable|in:mudah,sedang,sulit',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,webm|max:10240',
        ]);

        // Handle audio upload
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('questions/audio', 'public');
            $data['media'] = ['audio' => $path, 'type' => 'audio'];
        }

        $data['created_by'] = auth()->id();
        Question::create($data);
        return back()->with('success','Soal ditambahkan.');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $data = $request->validate([
            'question_bank_id' => 'required|exists:question_banks,id',
            'learning_objective_id' => 'nullable|exists:learning_objectives,id',
            'type' => 'required|in:pg,bs,jodoh,esai,audio',
            'content' => 'required|string',
            'options' => 'nullable|json',
            'answer_key' => 'nullable|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'level_kognitif' => 'nullable|in:L1,L2,L3',
            'difficulty' => 'nullable|in:mudah,sedang,sulit',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,webm|max:10240',
        ]);

        // Handle audio upload
        if ($request->hasFile('audio_file')) {
            // Hapus audio lama jika ada
            if (!empty($question->media['audio'])) {
                Storage::disk('public')->delete($question->media['audio']);
            }
            $path = $request->file('audio_file')->store('questions/audio', 'public');
            $data['media'] = ['audio' => $path, 'type' => 'audio'];
        }

        $question->update($data);
        return back()->with('success', 'Soal diperbarui.');
    }

    // ─── EXAMS ──────────────────────────────────────────────────────────
    public function exams(Request $request)
    {
        $user = auth()->user();
        $query = Exam::with(['subject','semester','creator'])
            ->where('school_id', $this->schoolId());

        // Guru hanya lihat ujian untuk mapel yang diampu
        if ($user->role === 'guru') {
            $taughtSubjectIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $query->whereIn('subject_id', $taughtSubjectIds);
        }

        $exams = $query->when($request->type, fn($q,$t)=>$q->where('type',$t))
            ->when($request->status, fn($q,$s)=>$q->where('status',$s))
            ->orderBy('created_at','desc')->paginate(15)->withQueryString();

        $subjectQuery = Subject::where('school_id', $this->schoolId())->where('is_active',true);
        $classQuery = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true);
        $bankQuery = QuestionBank::with('questions')->where('school_id', $this->schoolId());
        if ($user->role === 'guru') {
            $taughtSubjectIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $taughtClassIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $subjectQuery->whereIn('id', $taughtSubjectIds);
            $classQuery->whereIn('id', $taughtClassIds);
            $bankQuery->whereIn('subject_id', $taughtSubjectIds);
        }
        $subjects = $subjectQuery->get();
        $classes = $classQuery->get();
        $semesters = Semester::whereHas('academicYear', fn($q)=>$q->where('school_id',$this->schoolId()))->get();
        $banks = $bankQuery->get();

        // Data untuk edit modal (JS)
        $examData = $exams->keyBy('id')->map(function($e) {
            return [
                'code' => $e->code,
                'title' => $e->title,
                'type' => $e->type,
                'subject_id' => $e->subject_id,
                'class_ids' => $e->class_ids,
                'semester_id' => $e->semester_id,
                'start_time' => \Carbon\Carbon::parse($e->start_time)->format('Y-m-d\TH:i'),
                'end_time' => \Carbon\Carbon::parse($e->end_time)->format('Y-m-d\TH:i'),
                'duration' => $e->duration,
                'random_questions' => $e->random_questions,
                'random_answers' => $e->random_answers,
                'show_result' => $e->show_result,
                'status' => $e->status,
            ];
        });

        return view('backend.exam.list', compact('exams','subjects','classes','semesters','banks','examData'));
    }

    public function storeExam(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:30',
            'title' => 'required|string|max:200',
            'type' => 'required|in:uh,sts,sas,asaj,tryout',
            'subject_id' => 'required|exists:subjects,id',
            'class_ids' => 'required|array',
            'semester_id' => 'required|exists:semesters,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'random_questions' => 'boolean',
            'random_answers' => 'boolean',
            'show_result' => 'boolean',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['class_ids'] = json_encode($data['class_ids']);
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';
        if (empty($data['code'])) $data['code'] = strtoupper(substr($data['type'],0,3)).'-'.now()->format('ymd-His');
        Exam::create($data);
        return back()->with('success','Paket ujian dibuat.');
    }

    public function updateExam(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:30',
            'title' => 'required|string|max:200',
            'type' => 'required|in:uh,sts,sas,asaj,tryout',
            'subject_id' => 'required|exists:subjects,id',
            'class_ids' => 'required|array',
            'semester_id' => 'required|exists:semesters,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'random_questions' => 'boolean',
            'random_answers' => 'boolean',
            'show_result' => 'boolean',
            'status' => 'required|in:draft,published,ongoing,finished',
        ]);
        $data['class_ids'] = json_encode($data['class_ids']);
        if (empty($data['code'])) $data['code'] = strtoupper(substr($data['type'], 0, 3)) . '-' . now()->format('ymd-His');
        $exam->update($data);
        return back()->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroyExam(Exam $exam)
    {
        // Hapus relasi: exam_questions, exam_sessions & answers, exam_results
        $exam->examQuestions()->delete();
        $exam->sessions()->each(function ($s) { $s->answers()->delete(); $s->delete(); });
        $exam->results()->delete();
        $exam->delete();
        return back()->with('success', 'Ujian berhasil dihapus.');
    }

    public function addExamQuestions(Request $request, Exam $exam)
    {
        $request->validate(['question_ids' => 'required|array']);
        $exam->examQuestions()->delete();
        foreach ($request->question_ids as $i => $qid) {
            ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_id' => $qid,
                'urutan' => $i + 1,
            ]);
        }
        $exam->update(['total_questions' => count($request->question_ids), 'status' => 'published']);
        return back()->with('success','Soal ditambahkan ke ujian.');
    }

    // ─── RESULTS ────────────────────────────────────────────────────────
    public function results(Request $request)
    {
        $user = auth()->user();
        $query = Exam::with('subject')->where('school_id', $this->schoolId())
            ->whereIn('status', ['published','ongoing','finished']);

        // Guru hanya lihat hasil ujian untuk mapel yang diampu
        if ($user->role === 'guru') {
            $taughtSubjectIds = \App\Models\ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $query->whereIn('subject_id', $taughtSubjectIds);
        }

        $exams = $query->orderBy('start_time','desc')->get();
        $examId = $request->exam_id ?? optional($exams->first())->id;
        $results = collect();
        $sessions = collect();

        if ($examId) {
            $results = ExamResult::with(['student','exam.subject'])
                ->where('exam_id', $examId)->orderBy('total_score','desc')->get();
            $sessions = ExamSession::with('answers.examQuestion.question')
                ->where('exam_id', $examId)->get()->keyBy('id');
        }

        return view('backend.exam.results', compact('exams','examId','results','sessions'));
    }

    public function deleteQuestion(Question $question)
    {
        $question->delete();
        return back()->with('success','Soal dihapus.');
    }

    public function gradeEssay(Request $request, ExamResult $result)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0',
        ]);

        $totalScore = 0;
        foreach ($request->scores as $answerId => $score) {
            $answer = ExamAnswer::find($answerId);
            if ($answer) {
                $answer->update(['score' => $score, 'is_correct' => $score > 0]);
                $totalScore += $score;
            }
        }

        $result->update([
            'total_score' => $totalScore,
            'graded_by' => auth()->id(),
            'graded_at' => now(),
            'is_passed' => $totalScore >= ($result->exam->total_score * 0.7),
        ]);

        return back()->with('success','Nilai esai disimpan.');
    }
}
