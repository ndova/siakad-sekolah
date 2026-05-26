<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Curriculum;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\LearningObjectiveSubject;
use App\Models\LearningOutcome;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    protected function schoolId() { return auth()->user()->school_id; }
    protected function activeYearId() { return \App\Models\AcademicYear::where('school_id',$this->schoolId())->where('is_active',true)->value('id'); }
    protected function activeSemesterId() { return Semester::whereHas('academicYear',fn($q)=>$q->where('school_id',$this->schoolId()))->where('is_active',true)->value('id'); }

    // ─── CURRICULUM ────────────────────────────────────────────────────
    public function curriculum(Request $request)
    {
        $curricula = Curriculum::where('school_id', $this->schoolId())->with('academicYear')->orderBy('created_at','desc')->get();
        $subjects  = Subject::where('school_id', $this->schoolId())->where('is_active', true)->orderBy('code')->get();
        $curriculum = $request->curriculum_id ? Curriculum::with(['learningOutcomes.learningObjectives'])->find($request->curriculum_id) : $curricula->first();
        $classSubjects = ClassSubject::whereHas('schoolClass', fn($q)=>$q->where('school_id',$this->schoolId()))->with(['schoolClass','subject'])->paginate(20)->withQueryString();
        $semesters = Semester::whereHas('academicYear', fn($q)=>$q->where('school_id',$this->schoolId()))->where('is_active',true)->get();
        $activeYearId = $this->activeYearId();
        return view('backend.academic.curriculum', compact('curricula','subjects','curriculum','classSubjects','semesters','activeYearId'));
    }

    public function storeCurriculum(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;
        Curriculum::create($data);
        return back()->with('success','Kurikulum ditambahkan.');
    }

    public function storeCP(Request $request)
    {
        $data = $request->validate([
            'curriculum_id' => 'required|exists:curricula,id',
            'subject_id' => 'required|exists:subjects,id',
            'phase' => 'required|string|max:5',
            'code' => 'required|string|max:30',
            'description' => 'required|string',
            'urutan' => 'nullable|integer',
        ]);
        LearningOutcome::create($data);
        return back()->with('success','CP ditambahkan.');
    }

    public function storeTP(Request $request)
    {
        $data = $request->validate([
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'code' => 'required|string|max:30',
            'description' => 'required|string',
            'level_kognitif' => 'nullable|in:L1,L2,L3',
            'urutan' => 'nullable|integer',
        ]);
        LearningObjective::create($data);
        return back()->with('success','TP ditambahkan.');
    }

    public function storeATP(Request $request)
    {
        $data = $request->validate([
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'class_subject_id' => 'required|exists:class_subject,id',
            'semester_id' => 'required|exists:semesters,id',
            'urutan_ajar' => 'nullable|integer',
        ]);
        LearningObjectiveSubject::create($data);
        return back()->with('success','ATP mapping disimpan.');
    }

    // ─── GRADES ─────────────────────────────────────────────────────────
    public function grades(Request $request)
    {
        $user = auth()->user();
        $classSubjects = collect();
        
        if (in_array($user->role, ['guru', 'walikelas'])) {
            $classSubjects = ClassSubject::with(['schoolClass','subject'])
                ->where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q)=>$q->where('school_id',$this->schoolId()))
                ->get();
        } else {
            $classSubjects = ClassSubject::with(['schoolClass','subject'])
                ->whereHas('schoolClass', fn($q)=>$q->where('school_id',$this->schoolId()))
                ->get();
        }

        $classSubjectId = $request->class_subject_id ?? optional($classSubjects->first())->id;
        $semesterId = $request->semester_id ?? $this->activeSemesterId();
        $jenisNilai  = $request->jenis_nilai ?? 'uh';
        
        $students = collect();
        $tps = collect();
        $grades = collect();
        
        if ($classSubjectId) {
            $cs = ClassSubject::with('schoolClass')->find($classSubjectId);
            if ($cs) {
                $students = Student::where('school_id', $this->schoolId())
                    ->where('class_id', $cs->schoolClass->id)
                    ->where('status', 'aktif')->orderBy('nama_lengkap')->get();
                $tps = LearningObjectiveSubject::where('class_subject_id', $classSubjectId)
                    ->when($semesterId, fn($q)=>$q->where('semester_id',$semesterId))
                    ->with('learningObjective')->get();
                $grades = Grade::where('class_subject_id', $classSubjectId)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->when($semesterId, fn($q)=>$q->where('semester_id',$semesterId))
                    ->when($jenisNilai, fn($q)=>$q->where('jenis_nilai', $jenisNilai))
                    ->get()
                    ->keyBy(function($g) { return $g->student_id . '_' . $g->learning_objective_id; });
            }
        }

        $semesters = Semester::whereHas('academicYear', fn($q)=>$q->where('school_id',$this->schoolId()))->orderBy('semester_number')->get();

        return view('backend.academic.grades', compact(
            'classSubjects','classSubjectId','students','tps','grades',
            'semesterId','semesters','jenisNilai'
        ));
    }

    public function storeGrade(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_subject_id' => 'required|exists:class_subject,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'semester_id' => 'required|exists:semesters,id',
            'jenis_nilai' => 'required|in:uh,sts,sas,p5,tugas',
            'nilai' => 'required|numeric|min:0|max:100',
            'deskripsi' => 'nullable|string',
        ]);
        $data['sumber'] = 'manual';
        $data['created_by'] = auth()->id();

        Grade::updateOrCreate(
            ['student_id'=>$data['student_id'], 'class_subject_id'=>$data['class_subject_id'],
             'learning_objective_id'=>$data['learning_objective_id'], 'jenis_nilai'=>$data['jenis_nilai'],
             'semester_id'=>$data['semester_id']],
            $data
        );
        return back()->with('success','Nilai disimpan.');
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'class_subject_id' => 'required|exists:class_subject,id',
            'semester_id'      => 'required|exists:semesters,id',
            'jenis_nilai'      => 'required|in:uh,sts,sas,p5,tugas',
            'nilai'            => 'required|array',
        ]);

        $count = 0;
        $deletedCount = 0;
        foreach ($data['nilai'] as $studentId => $tpNilai) {
            if (!is_array($tpNilai)) continue;
            foreach ($tpNilai as $losId => $nilai) {
                // Resolve LearningObjective ID from LearningObjectiveSubject pivot
                $los = LearningObjectiveSubject::with('learningObjective')->find($losId);
                if (!$los?->learningObjective) continue;

                // Empty value → hapus grade yang sudah ada
                if ($nilai === '' || $nilai === null) {
                    $deleted = Grade::where([
                        'student_id'           => $studentId,
                        'class_subject_id'     => $data['class_subject_id'],
                        'learning_objective_id' => $los->learning_objective_id,
                        'jenis_nilai'          => $data['jenis_nilai'],
                        'semester_id'          => $data['semester_id'],
                    ])->delete();
                    if ($deleted) $deletedCount++;
                    continue;
                }

                $nilaiVal = (float) $nilai;
                if ($nilaiVal < 0 || $nilaiVal > 100) continue;

                Grade::updateOrCreate(
                    [
                        'student_id'           => $studentId,
                        'class_subject_id'     => $data['class_subject_id'],
                        'learning_objective_id' => $los->learning_objective_id,
                        'jenis_nilai'          => $data['jenis_nilai'],
                        'semester_id'          => $data['semester_id'],
                    ],
                    [
                        'nilai'      => $nilaiVal,
                        'sumber'     => 'manual',
                        'created_by' => auth()->id(),
                    ]
                );
                $count++;
            }
        }
        $msg = "$count nilai berhasil disimpan.";
        if ($deletedCount > 0) {
            $msg .= " $deletedCount nilai dihapus.";
        }
        return back()->with('success', $msg);
    }

    /**
     * Hapus satu nilai (grade) per TP.
     */
    public function deleteGrade(Grade $grade)
    {
        $grade->delete();
        return back()->with('success', 'Nilai berhasil dihapus.');
    }

    // ─── REPORTS ────────────────────────────────────────────────────────
    public function reports(Request $request)
    {
        $user = auth()->user();
        $classId = $request->class_id ?? optional($user->homeroomClass)->id;
        $semesterId = $request->semester_id ?? $this->activeSemesterId();

        $classQuery = SchoolClass::where('school_id', $this->schoolId())->where('is_active', true);
        // Guru hanya lihat kelas yang diampu
        if ($user->role === 'guru') {
            $taughtIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $classQuery->whereIn('id', $taughtIds);
            if (!$classId || !$taughtIds->contains($classId)) {
                $classId = $taughtIds->first();
            }
        }
        $classes = $classQuery->orderBy('code')->get();
        $students = collect();
        $reports = collect();
        
        if ($classId) {
            $students = Student::with('user')->where('class_id', $classId)
                ->where('status', 'aktif')->orderBy('nama_lengkap')->paginate(25)->withQueryString();
            if ($semesterId) {
                $reports = Report::whereIn('student_id', $students->pluck('id'))
                    ->where('semester_id', $semesterId)
                    ->with('classSubject.subject')
                    ->get()->groupBy('student_id');
            }
        }

        $classSubjects = ClassSubject::whereHas('schoolClass', fn($q)=>$q->where('school_id',$this->schoolId()))
            ->with('subject')->get()->groupBy('class_id');

        $semesters = Semester::whereHas('academicYear', fn($q)=>$q->where('school_id',$this->schoolId()))->get();

        return view('backend.academic.reports', compact('classes','classId','students','reports','classSubjects','semesterId','semesters'));
    }

    /**
     * Kunci rapor — generate nilai akhir dari grades lalu lock.
     * Menerima student_ids (checkbox) atau fallback ke seluruh kelas.
     */
    public function lockReports(Request $request)
    {
        $validated = $request->validate([
            'semester_id'  => 'required|exists:semesters,id',
            'class_id'     => 'required|exists:classes,id',
            'student_ids'  => 'nullable|array',
            'student_ids.*'=> 'exists:students,id',
        ]);

        $semesterId = $validated['semester_id'];
        $classId    = $validated['class_id'];
        $studentIds = $validated['student_ids'] ?? [];

        // Jika tidak ada yang dipilih, ambil semua siswa aktif di kelas
        if (empty($studentIds)) {
            $studentIds = Student::where('class_id', $classId)
                ->where('status', 'aktif')
                ->pluck('id')
                ->toArray();
        }

        $classSubjects = ClassSubject::where('class_id', $classId)->get();
        $userId = auth()->id();
        $now    = now();
        $lockedCount = 0;

        foreach ($studentIds as $studentId) {
            foreach ($classSubjects as $cs) {
                // Ambil rata-rata dari semua jenis nilai (formative + summative)
                $rataRata = Grade::where('student_id', $studentId)
                    ->where('class_subject_id', $cs->id)
                    ->where('semester_id', $semesterId)
                    ->avg('nilai');

                // Cek apakah sudah ada report manual (dari form edit)
                $existing = Report::where([
                    'student_id'      => $studentId,
                    'semester_id'     => $semesterId,
                    'class_subject_id' => $cs->id,
                ])->first();

                // Jika sudah ada nilai manual dan belum terkunci, gunakan nilai manual
                // Jika tidak, hitung dari grades
                if ($existing && !$existing->is_locked && $existing->nilai_akhir !== null) {
                    $nilaiAkhir = $existing->nilai_akhir;
                    $predikat   = $existing->predikat;
                    $deskripsi  = $existing->deskripsi_cp;
                } elseif ($rataRata !== null) {
                    $nilaiAkhir = round((float) $rataRata, 1);
                    $predikat   = match(true) {
                        $rataRata >= 90 => 'A',
                        $rataRata >= 80 => 'B',
                        $rataRata >= 70 => 'C',
                        default         => 'D',
                    };
                    $deskripsi = $existing->deskripsi_cp ?? null;
                } else {
                    continue; // Tidak ada nilai — skip
                }

                Report::updateOrCreate(
                    [
                        'student_id'       => $studentId,
                        'semester_id'      => $semesterId,
                        'class_subject_id'  => $cs->id,
                    ],
                    [
                        'nilai_akhir'  => $nilaiAkhir,
                        'predikat'     => $predikat,
                        'deskripsi_cp' => $deskripsi,
                        'is_locked'    => true,
                        'locked_by'    => $userId,
                        'locked_at'    => $now,
                    ]
                );
                $lockedCount++;
            }
        }

        return back()->with('success', "$lockedCount entri rapor berhasil dikunci.");
    }

    /**
     * Buka kunci rapor — set is_locked = false.
     */
    public function unlockReports(Request $request)
    {
        $validated = $request->validate([
            'semester_id'  => 'required|exists:semesters,id',
            'student_ids'  => 'required|array|min:1',
            'student_ids.*'=> 'exists:students,id',
        ]);

        $semesterId = $validated['semester_id'];
        $studentIds = $validated['student_ids'];

        $unlockedCount = Report::where('semester_id', $semesterId)
            ->whereIn('student_id', $studentIds)
            ->where('is_locked', true)
            ->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null,
            ]);

        return back()->with('success', "$unlockedCount entri rapor berhasil dibuka kuncinya.");
    }

    /**
     * Toggle kunci/buka per siswa — POST dari halaman daftar rapor.
     */
    public function toggleLock(Request $request, Student $student)
    {
        $semesterId = $request->semester_id ?? $this->activeSemesterId();
        $classSubjects = ClassSubject::where('class_id', $student->class_id)->get();

        // Cek apakah sudah terkunci
        $isLocked = Report::where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            // UNLOCK
            Report::where('student_id', $student->id)
                ->where('semester_id', $semesterId)
                ->update([
                    'is_locked' => false,
                    'locked_by' => null,
                    'locked_at' => null,
                ]);
            return back()->with('success', "Rapor {$student->nama_lengkap} berhasil dibuka kuncinya.");
        } else {
            // LOCK — generate dari grades
            $userId = auth()->id();
            $now    = now();
            $count  = 0;

            foreach ($classSubjects as $cs) {
                $rataRata = Grade::where('student_id', $student->id)
                    ->where('class_subject_id', $cs->id)
                    ->where('semester_id', $semesterId)
                    ->avg('nilai');

                if ($rataRata === null) continue;

                $nilaiAkhir = round((float) $rataRata, 1);
                $predikat   = match(true) {
                    $rataRata >= 90 => 'A',
                    $rataRata >= 80 => 'B',
                    $rataRata >= 70 => 'C',
                    default         => 'D',
                };

                // Pertahankan deskripsi manual jika ada
                $existing = Report::where([
                    'student_id'       => $student->id,
                    'semester_id'      => $semesterId,
                    'class_subject_id'  => $cs->id,
                ])->first();

                Report::updateOrCreate(
                    [
                        'student_id'       => $student->id,
                        'semester_id'      => $semesterId,
                        'class_subject_id'  => $cs->id,
                    ],
                    [
                        'nilai_akhir'  => $nilaiAkhir,
                        'predikat'     => $predikat,
                        'deskripsi_cp' => $existing->deskripsi_cp ?? null,
                        'is_locked'    => true,
                        'locked_by'    => $userId,
                        'locked_at'    => $now,
                    ]
                );
                $count++;
            }

            return back()->with('success', "Rapor {$student->nama_lengkap} berhasil dikunci ($count mapel).");
        }
    }

    /**
     * Form edit rapor untuk satu siswa — semua mapel dalam satu halaman.
     */
    public function editReport(Request $request, Student $student)
    {
        $schoolId = $this->schoolId();
        $school = \App\Models\School::find($schoolId);
        $class = $student->class;
        $semesterId = $request->semester_id ?? $this->activeSemesterId();
        $semester = Semester::with('academicYear')->find($semesterId);

        if (!$semester || !$class) {
            return back()->with('error', 'Data semester atau kelas tidak ditemukan.');
        }

        // ─── Mapel ───
        $classSubjects = ClassSubject::with(['subject'])
            ->where('class_id', $class->id)
            ->orderBy('id')
            ->get();

        // ─── Report yang sudah ada ───
        $existingReports = Report::where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy('class_subject_id');

        $semesters = Semester::whereHas('academicYear', fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('semester_number')
            ->get();

        return view('backend.academic.report_form', compact(
            'school', 'student', 'class', 'semester', 'semesterId',
            'classSubjects', 'existingReports', 'semesters'
        ));
    }

    /**
     * Simpan / update rapor per siswa (semua mapel sekaligus).
     */
    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'semester_id'   => 'required|exists:semesters,id',
            'nilai_akhir'   => 'required|array',
            'nilai_akhir.*' => 'nullable|numeric|min:0|max:100',
            'predikat'      => 'required|array',
            'predikat.*'    => 'nullable|in:A,B,C,D',
            'deskripsi_cp'  => 'required|array',
            'deskripsi_cp.*'=> 'nullable|string|max:1000',
        ]);

        $studentId  = $validated['student_id'];
        $semesterId = $validated['semester_id'];
        $userId     = auth()->id();

        // Ambil semua report yang sudah di-lock — jangan boleh diedit
        $lockedIds = Report::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('is_locked', true)
            ->pluck('class_subject_id')
            ->toArray();

        foreach ($validated['nilai_akhir'] as $csId => $nilai) {
            // Skip kalau sudah di-lock
            if (in_array($csId, $lockedIds)) {
                continue;
            }

            // Skip kalau semua field kosong
            $nilaiAkhir = $nilai !== null && $nilai !== '' ? round((float) $nilai, 1) : null;
            $predikat   = $validated['predikat'][$csId] ?? null;
            $deskripsi  = $validated['deskripsi_cp'][$csId] ?? null;

            if ($nilaiAkhir === null && !$predikat && !$deskripsi) {
                // Hapus report yang ada jika semua kosong
                Report::where([
                    'student_id'      => $studentId,
                    'semester_id'     => $semesterId,
                    'class_subject_id' => $csId,
                    'is_locked'       => false,
                ])->delete();
                continue;
            }

            // Update or create
            Report::updateOrCreate(
                [
                    'student_id'      => $studentId,
                    'semester_id'     => $semesterId,
                    'class_subject_id' => $csId,
                ],
                [
                    'nilai_akhir'  => $nilaiAkhir,
                    'predikat'     => $predikat,
                    'deskripsi_cp' => $deskripsi,
                    'is_locked'    => false,
                    'locked_by'    => null,
                    'locked_at'    => null,
                ]
            );
        }

        return redirect()
            ->route('academic.reports', [
                'class_id'    => $request->class_id,
                'semester_id' => $semesterId,
            ])
            ->with('success', 'Rapor berhasil disimpan.');
    }

    /**
     * Hapus satu entri rapor.
     */
    public function deleteReport(Report $report)
    {
        if ($report->is_locked) {
            return back()->with('error', 'Rapor yang sudah terkunci tidak dapat dihapus.');
        }

        $semesterId = $report->semester_id;
        $student    = $report->student;
        $classId    = $student->class_id ?? null;

        $report->delete();

        return redirect()
            ->route('academic.reports', [
                'class_id'    => $classId,
                'semester_id' => $semesterId,
            ])
            ->with('success', 'Rapor berhasil dihapus.');
    }

    /**
     * Tampilkan rapor detail per siswa (Kurikulum Merdeka)
     */
    public function showReport(Request $request, Student $student)
    {
        $schoolId = $this->schoolId();
        $school = \App\Models\School::find($schoolId);
        $class = $student->class;
        $semesterId = $request->semester_id ?? $this->activeSemesterId();
        $semester = Semester::with('academicYear')->find($semesterId);

        if (!$semester || !$class) {
            return back()->with('error', 'Data semester atau kelas tidak ditemukan.');
        }

        // ─── Fase (Kurikulum Merdeka) ───
        $tingkat = $class->tingkat ?? 10;
        $phase = match(true) {
            $tingkat <= 2 => 'A',
            $tingkat <= 4 => 'B',
            $tingkat <= 6 => 'C',
            $tingkat <= 9 => 'D',
            default => 'E',
        };

        // ─── Mapel & Nilai ───
        $classSubjects = ClassSubject::with(['subject'])
            ->where('class_id', $class->id)
            ->orderBy('id')
            ->get();

        $allGrades = Grade::where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->whereIn('class_subject_id', $classSubjects->pluck('id'))
            ->get()
            ->groupBy('class_subject_id');

        // Get reports (if any locked)
        $reports = Report::where('student_id', $student->id)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy('class_subject_id');

        $mapelList = [];
        foreach ($classSubjects as $cs) {
            $csGrades = $allGrades->get($cs->id, collect());
            $report = $reports->get($cs->id);

            // TP details
            $tpList = [];
            $tps = LearningObjectiveSubject::with('learningObjective')
                ->where('class_subject_id', $cs->id)
                ->where('semester_id', $semesterId)
                ->orderBy('urutan_ajar')
                ->get();

            $totalNilai = 0;
            $countNilai = 0;
            foreach ($tps as $los) {
                $tpGrades = $csGrades->where('learning_objective_id', $los->learningObjective->id);
                $formatif = $tpGrades->where('jenis_nilai', 'formatif')->avg('nilai');
                $sumatif = $tpGrades->where('jenis_nilai', 'sumatif')->avg('nilai');

                // Fallback: semua jenis nilai dirata-rata
                if ($formatif === null && $sumatif === null) {
                    $allForTp = $tpGrades->avg('nilai');
                    $tpAvg = $allForTp;
                } else {
                    $vals = array_filter([$formatif, $sumatif], fn($v) => $v !== null);
                    $tpAvg = count($vals) > 0 ? array_sum($vals) / count($vals) : null;
                }

                if ($tpAvg !== null) {
                    $totalNilai += $tpAvg;
                    $countNilai++;
                }

                $tpList[] = [
                    'code' => $los->learningObjective->code,
                    'description' => $los->learningObjective->description,
                    'formatif' => $formatif !== null ? number_format($formatif, 1) : null,
                    'sumatif' => $sumatif !== null ? number_format($sumatif, 1) : null,
                    'rata_rata' => $tpAvg !== null ? round($tpAvg, 1) : null,
                ];
            }

            // Nilai Akhir = rata-rata semua TP, atau dari report jika di-lock
            $nilaiAkhir = $report?->nilai_akhir
                ?? ($countNilai > 0 ? round($totalNilai / $countNilai, 1) : null);

            // Predikat
            $predikat = $report?->predikat
                ?? match(true) {
                    $nilaiAkhir >= 90 => 'A',
                    $nilaiAkhir >= 80 => 'B',
                    $nilaiAkhir >= 70 => 'C',
                    $nilaiAkhir !== null => 'D',
                    default => null,
                };

            // Deskripsi CP (auto-generate jika belum ada)
            $deskripsi = $report?->deskripsi_cp;
            if (!$deskripsi && $nilaiAkhir !== null) {
                if ($nilaiAkhir >= 90) {
                    $deskripsi = "Menguasai seluruh capaian pembelajaran dengan sangat baik pada mata pelajaran {$cs->subject->name}.";
                } elseif ($nilaiAkhir >= 80) {
                    $deskripsi = "Menguasai sebagian besar capaian pembelajaran dengan baik pada mata pelajaran {$cs->subject->name}.";
                } elseif ($nilaiAkhir >= 70) {
                    $deskripsi = "Menguasai capaian pembelajaran dengan cukup pada mata pelajaran {$cs->subject->name}. Perlu pendampingan pada beberapa TP.";
                } else {
                    $deskripsi = "Belum mencapai ketuntasan pada beberapa TP mata pelajaran {$cs->subject->name}. Disarankan mengikuti remedial.";
                }
            }

            $mapelList[] = [
                'cs_id' => $cs->id,
                'nama' => $cs->subject->name,
                'kkm' => $cs->kkm,
                'nilai_akhir' => $nilaiAkhir,
                'predikat' => $predikat,
                'deskripsi' => $deskripsi,
                'tp_count' => count($tpList),
                'tps' => $tpList,
                'is_locked' => $report?->is_locked ?? false,
            ];
        }

        // ─── P5 ───
        $p5Projects = [];
        $studentAssessments = P5Assessment::where('student_id', $student->id)
            ->with('p5Project')
            ->get();

        foreach ($studentAssessments as $assess) {
            $proj = $assess->p5Project;
            $dimensi = [];
            for ($i = 1; $i <= 6; $i++) {
                $field = "dimensi_{$i}";
                if ($assess->$field) {
                    $dimensi[$i - 1] = $assess->$field;
                }
            }
            $p5Projects[] = [
                'tema' => $proj->tema,
                'judul' => $proj->judul,
                'deskripsi' => $proj->deskripsi,
                'dimensi' => $dimensi,
                'catatan' => $assess->catatan_proses,
            ];
        }

        // ─── Presensi ───
        $attendanceRecap = Attendance::recapForStudent($student->id, $semesterId);

        // ─── Catatan Wali Kelas ───
        $catatanWalikelas = null;
        $waliKelas = $class->waliKelas;

        // ─── Kepala Sekolah ───
        $kepalaSekolah = \App\Models\Staff::where('school_id', $school->id)
            ->where('jabatan', 'kepsek')
            ->where('is_active', true)
            ->first();

        // ─── Orang Tua / Wali siswa ───
        $orangTua = $student->parents()
            ->wherePivot('is_primary', true)
            ->first();

        // Pilih view rapor berdasarkan kurikulum aktif
        $curriculum = \App\Enums\CurriculumType::fromSession();
        $view = $curriculum === \App\Enums\CurriculumType::KURMER
            ? 'backend.academic.report_card'
            : 'backend.academic.report_card_k13';

        return view($view, compact(
            'school', 'student', 'class', 'semester', 'phase',
            'mapelList', 'p5Projects', 'attendanceRecap', 'catatanWalikelas',
            'waliKelas', 'kepalaSekolah', 'orangTua'
        ));
    }

    // ─── ATTENDANCE ─────────────────────────────────────────────────────

    /**
     * Halaman presensi utama (berdasarkan kelas + tanggal)
     */
    public function attendance(Request $request)
    {
        $user = auth()->user();
        $classId = $request->class_id ?? null;
        $classSubjectId = $request->class_subject_id ?? null;
        $tanggal = $request->tanggal ?? now()->format('Y-m-d');
        $semesterId = $this->activeSemesterId();

        // Jika guru/walikelas, tampilkan hanya kelas yang diampu
        $myClassSubjects = collect();
        if (in_array($user->role, ['guru', 'walikelas'])) {
            $myClassSubjects = ClassSubject::with(['schoolClass','subject'])
                ->where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->get();
            // Default ke kelas pertama yang diampu
            if (!$classId && $myClassSubjects->isNotEmpty()) {
                $classId = $myClassSubjects->first()->schoolClass->id;
            }
        }

        $classes = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true)->orderBy('code')->get();
        $students = collect();
        $attendances = collect();
        $classSubject = null;

        if ($classSubjectId) {
            $classSubject = ClassSubject::with(['subject', 'schoolClass'])->findOrFail($classSubjectId);
            $students = Student::where('class_id', $classSubject->class_id)->where('status','aktif')->orderBy('nama_lengkap')->get();
            $attendances = Attendance::where('class_subject_id', $classSubjectId)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereDate('tanggal', $tanggal)
                ->get()->keyBy('student_id');
        } elseif ($classId) {
            $students = Student::where('class_id', $classId)->where('status','aktif')->orderBy('nama_lengkap')->get();
            $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
                ->whereDate('tanggal', $tanggal)
                ->get()->keyBy('student_id');
        }

        return view('backend.academic.attendance', compact(
            'classes','classId','classSubjectId','classSubject','tanggal','students','attendances','semesterId','myClassSubjects'
        ));
    }

    /**
     * Simpan presensi per siswa
     */
    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'tanggal' => 'required|date',
            'class_subject_id' => 'nullable|exists:class_subject,id',
            'status' => 'required|in:hadir,izin,sakit,alfa,terlambat,tidak_hadir',
            'keterangan' => 'nullable|string|max:255',
        ]);
        $data['semester_id'] = $this->activeSemesterId();
        $data['created_by'] = auth()->id();

        $uniqueKey = ['student_id' => $data['student_id'], 'tanggal' => $data['tanggal']];
        if (!empty($data['class_subject_id'])) {
            $uniqueKey['class_subject_id'] = $data['class_subject_id'];
        }

        Attendance::updateOrCreate($uniqueKey, $data);
        return back()->with('success','Presensi disimpan.');
    }

    /**
     * Simpan presensi massal
     */
    public function bulkAttendance(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'class_subject_id' => 'nullable|exists:class_subject,id',
            'class_id' => 'nullable|exists:classes,id',
            'status' => 'required|array',
            'status.*' => 'required|in:hadir,izin,sakit,alfa,terlambat,tidak_hadir',
            'keterangan' => 'nullable|array',
        ]);

        $semesterId = $this->activeSemesterId();
        foreach ($data['status'] as $studentId => $status) {
            $uniqueKey = ['student_id' => $studentId, 'tanggal' => $data['tanggal']];
            if (!empty($data['class_subject_id'])) {
                $uniqueKey['class_subject_id'] = $data['class_subject_id'];
            }

            Attendance::updateOrCreate($uniqueKey, [
                'status' => $status,
                'class_subject_id' => $data['class_subject_id'] ?? null,
                'semester_id' => $semesterId,
                'created_by' => auth()->id(),
                'keterangan' => $data['keterangan'][$studentId] ?? null,
            ]);
        }
        return back()->with('success','Presensi massal disimpan.');
    }

    /**
     * Rekap presensi per kelas (wali kelas / admin)
     */
    public function attendanceRecap(Request $request)
    {
        $user = auth()->user();
        $classId = $request->class_id ?? null;
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);
        $semesterId = $this->activeSemesterId();
        $semester = Semester::with('academicYear')->find($semesterId);

        // Jika wali kelas, default ke kelasnya
        if (!$classId && $user->role === 'walikelas') {
            $homeroom = SchoolClass::where('wali_kelas_id', $user->id)->first();
            if ($homeroom) $classId = $homeroom->id;
        }

        $classes = SchoolClass::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $students = collect();
        $recapData = collect();
        $class = null;

        if ($classId) {
            $class = SchoolClass::with('waliKelas')->findOrFail($classId);
            $students = Student::where('class_id', $classId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap')
                ->paginate(25)->withQueryString();

            $attendanceQuery = Attendance::where('semester_id', $semesterId)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereRaw("strftime('%m', tanggal) = ?", [sprintf('%02d', (int)$month)])
                ->whereRaw("strftime('%Y', tanggal) = ?", [(string)$year]);

            $allAtt = $attendanceQuery->get()->groupBy('student_id');

            $recapData = $students->map(function ($student) use ($allAtt) {
                $records = $allAtt->get($student->id, collect());
                $total = $records->count();
                $hadir = $records->where('status', 'hadir')->count();
                $terlambat = $records->where('status', 'terlambat')->count();
                return (object)[
                    'student_id' => $student->id,
                    'nama' => $student->nama_lengkap,
                    'nis' => $student->nis,
                    'hadir' => $hadir,
                    'izin' => $records->where('status', 'izin')->count(),
                    'sakit' => $records->where('status', 'sakit')->count(),
                    'alfa' => $records->where('status', 'alfa')->count() + $records->where('status', 'tidak_hadir')->count(),
                    'terlambat' => $terlambat,
                    'total' => $total,
                    'persentase' => $total > 0 ? round((($hadir + $terlambat) / $total) * 100, 1) : 0,
                ];
            });
        }

        return view('backend.academic.attendance-recap', compact(
            'classes','classId','class','month','year',
            'semesterId','semester','students','recapData'
        ));
    }

    // ─── P5 ─────────────────────────────────────────────────────────────
    public function p5(Request $request)
    {
        $projects = P5Project::with(['semester','creator'])
            ->where('school_id', $this->schoolId())
            ->orderBy('created_at','desc')->paginate(10)->withQueryString();
        $classes = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true)->orderBy('code')->get();
        $semesters = Semester::whereHas('academicYear', fn($q)=>$q->where('school_id',$this->schoolId()))->get();
        $projectId = $request->project_id ?? optional($projects->first())->id;
        $assessments = collect();
        $projectStudents = collect();

        if ($projectId) {
            $project = P5Project::find($projectId);
            $assessments = P5Assessment::where('p5_project_id', $projectId)->with('student')->get()->keyBy('student_id');
            if ($project && $project->class_ids) {
                $projectStudents = Student::whereIn('class_id', is_array($project->class_ids) ? $project->class_ids : json_decode($project->class_ids, true))
                    ->where('status','aktif')->orderBy('nama_lengkap')->get();
            }
        }

        return view('backend.academic.p5', compact('projects','classes','semesters','projectId','assessments','projectStudents'));
    }

    public function storeP5Project(Request $request)
    {
        $data = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'tema' => 'required|string|max:100',
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'class_ids' => 'required|array',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['class_ids'] = json_encode($data['class_ids']);
        $data['created_by'] = auth()->id();
        P5Project::create($data);
        return back()->with('success','Projek P5 dibuat.');
    }

    public function updateP5Project(Request $request, P5Project $project)
    {
        $data = $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'tema' => 'required|string|max:100',
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'class_ids' => 'required|array',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        $data['class_ids'] = json_encode($data['class_ids']);
        $project->update($data);
        return back()->with('success','Projek P5 diperbarui.');
    }

    public function destroyP5Project(P5Project $project)
    {
        $project->assessments()->delete();
        $project->delete();
        return back()->with('success','Projek P5 dihapus.');
    }

    public function storeP5Assessment(Request $request)
    {
        $data = $request->validate([
            'p5_project_id' => 'required|exists:p5_projects,id',
            'student_id' => 'required|exists:students,id',
            'dimensi_1' => 'nullable|in:BB,MB,BSH,SB',
            'dimensi_2' => 'nullable|in:BB,MB,BSH,SB',
            'dimensi_3' => 'nullable|in:BB,MB,BSH,SB',
            'dimensi_4' => 'nullable|in:BB,MB,BSH,SB',
            'dimensi_5' => 'nullable|in:BB,MB,BSH,SB',
            'dimensi_6' => 'nullable|in:BB,MB,BSH,SB',
            'catatan_proses' => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();

        P5Assessment::updateOrCreate(
            ['p5_project_id'=>$data['p5_project_id'], 'student_id'=>$data['student_id']],
            $data
        );
        return back()->with('success','Asesmen P5 disimpan.');
    }
}
