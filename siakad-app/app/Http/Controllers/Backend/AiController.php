<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiController extends Controller
{
    protected function schoolId() { return auth()->user()->school_id; }
    protected function activeSemesterId() { return Semester::whereHas('academicYear',fn($q)=>$q->where('school_id',$this->schoolId()))->where('is_active',true)->value('id'); }

    /**
     * Dashboard AI Analitik — insight cerdas dari data sekolah
     */
    public function index()
    {
        $user = auth()->user();
        $schoolId = $this->schoolId();
        $semesterId = $this->activeSemesterId();

        // ─── 1. Ringkasan Performa per Kelas ───
        $kelasPerform = collect();
        $classQuery = SchoolClass::where('school_id', $schoolId)->where('is_active', true);

        if ($user->role === 'guru') {
            $taughtIds = ClassSubject::where('teacher_id', $user->id)
                ->pluck('class_id')->unique();
            $classQuery->whereIn('id', $taughtIds);
        }
        $classes = $classQuery->orderBy('tingkat')->orderBy('code')->get();

        foreach ($classes as $class) {
            $students = Student::where('class_id', $class->id)->where('status', 'aktif')->pluck('id');
            if ($students->isEmpty()) continue;

            $grades = Grade::whereIn('student_id', $students)
                ->where('semester_id', $semesterId)
                ->selectRaw('student_id, AVG(nilai) as avg_nilai')
                ->groupBy('student_id')
                ->get();

            $avg = $grades->avg('avg_nilai');
            $count = $grades->count();

            // Hitung siswa berisiko (nilai di bawah KKM, misal < 70)
            $atRisk = $grades->filter(fn($g) => ($g->avg_nilai ?? 0) < 70)->count();

            $kelasPerform->push((object)[
                'code'          => $class->code,
                'name'          => $class->name,
                'total_siswa'   => $students->count(),
                'avg_nilai'     => $count > 0 ? round($avg, 1) : 0,
                'siswa_berisiko' => $atRisk,
                'tingkat'       => $class->tingkat,
            ]);
        }

        // ─── 2. Siswa Perlu Perhatian (at-risk) ───
        $siswaBerisiko = collect();
        $allStudents = Student::where('school_id', $schoolId)->where('status', 'aktif')
            ->with(['class', 'grades' => fn($q) => $q->where('semester_id', $semesterId)])
            ->get();

        foreach ($allStudents as $student) {
            $avgNilai = $student->grades->avg('nilai');
            if ($avgNilai === null) continue;

            // Hitung presensi
            $hadir = Attendance::where('student_id', $student->id)
                ->where('semester_id', $semesterId)
                ->whereIn('status', ['hadir', 'terlambat'])->count();
            $totalPresensi = Attendance::where('student_id', $student->id)
                ->where('semester_id', $semesterId)->count();

            $alfaCount = Attendance::where('student_id', $student->id)
                ->where('semester_id', $semesterId)
                ->whereIn('status', ['alfa', 'tidak_hadir'])->count();

            // Skor risiko: kombinasi nilai rendah + alfa tinggi
            $riskScore = 0;
            if ($avgNilai < 65) $riskScore += 3;
            elseif ($avgNilai < 75) $riskScore += 1;
            if ($totalPresensi > 0 && ($alfaCount / $totalPresensi) > 0.1) $riskScore += 2;
            if ($alfaCount > 5) $riskScore += 2;

            $trend = $this->hitungTrendNilai($student->id, $semesterId);

            if ($riskScore >= 3 || $trend === 'menurun') {
                $siswaBerisiko->push((object)[
                    'nama'       => $student->nama_lengkap,
                    'kelas'      => $student->class?->code ?? '-',
                    'avg_nilai'  => round($avgNilai, 1),
                    'alfa'       => $alfaCount,
                    'risk_score' => $riskScore,
                    'trend'      => $trend,
                    'rekomendasi' => $this->generateRekomendasi($avgNilai, $alfaCount, $trend),
                ]);
            }
        }
        $siswaBerisiko = $siswaBerisiko->sortByDesc('risk_score')->values();

        // ─── 3. Analisis per Mapel ───
        $mapelAnalisis = collect();
        $subjectQuery = Subject::where('school_id', $schoolId)->where('is_active', true);
        if ($user->role === 'guru') {
            $taughtSubjectIds = ClassSubject::where('teacher_id', $user->id)
                ->pluck('subject_id')->unique();
            $subjectQuery->whereIn('id', $taughtSubjectIds);
        }
        $subjects = $subjectQuery->orderBy('code')->get();

        foreach ($subjects as $subject) {
            $classSubjectIds = ClassSubject::where('subject_id', $subject->id)->pluck('id');
            if ($classSubjectIds->isEmpty()) continue;

            $grades = Grade::whereIn('class_subject_id', $classSubjectIds)
                ->where('semester_id', $semesterId)
                ->whereNotNull('nilai')
                ->get();

            if ($grades->isEmpty()) continue;

            $avg = $grades->avg('nilai');
            $count = $grades->count();
            $belowKkm = $grades->filter(fn($g) => $g->nilai < 70)->count();

            $mapelAnalisis->push((object)[
                'nama'         => $subject->name,
                'code'         => $subject->code,
                'avg_nilai'    => round($avg, 1),
                'total_nilai'  => $count,
                'below_kkm'    => $belowKkm,
                'pct_below'    => $count > 0 ? round(($belowKkm / $count) * 100, 1) : 0,
            ]);
        }

        // ─── 4. Rekomendasi TP/KD yang perlu diulang ───
        $tpRendah = collect();
        $lowGradeSubjectIds = $mapelAnalisis->filter(fn($m) => $m->avg_nilai < 75)->pluck('code');
        if ($lowGradeSubjectIds->isNotEmpty()) {
            $lowSubjects = Subject::where('school_id', $schoolId)->whereIn('code', $lowGradeSubjectIds)->pluck('id');
            $classSubjectIds = ClassSubject::whereIn('subject_id', $lowSubjects)->pluck('id');

            $tpRendah = Grade::whereIn('class_subject_id', $classSubjectIds)
                ->where('semester_id', $semesterId)
                ->whereNotNull('nilai')
                ->where('nilai', '<', 65)
                ->with(['learningObjective' => fn($q) => $q->with('learningOutcome.subject')])
                ->get()
                ->groupBy(fn($g) => $g->learningObjective?->learningOutcome?->subject?->name ?? 'Umum')
                ->map(fn($grades, $subjectName) => (object)[
                    'mapel' => $subjectName,
                    'total_rendah' => $grades->count(),
                    'tp_list' => $grades->pluck('learningObjective.description')
                        ->filter()->unique()->take(5)->values(),
                ])
                ->values();
        }

        return view('backend.ai.dashboard', compact(
            'kelasPerform', 'siswaBerisiko', 'mapelAnalisis', 'tpRendah'
        ));
    }

    /**
     * Hitung tren nilai siswa (naik / stabil / menurun)
     */
    private function hitungTrendNilai($studentId, $semesterId)
    {
        $grades = Grade::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->whereNotNull('nilai')
            ->orderBy('created_at')
            ->pluck('nilai');

        if ($grades->count() < 3) return 'stabil';

        $half = floor($grades->count() / 2);
        $firstHalf = $grades->take($half)->avg();
        $secondHalf = $grades->skip($half)->avg();

        if ($firstHalf === null || $secondHalf === null) return 'stabil';

        $diff = $secondHalf - $firstHalf;
        if ($diff < -5) return 'menurun';
        if ($diff > 5) return 'naik';
        return 'stabil';
    }

    /**
     * Generate rekomendasi berdasarkan data siswa
     */
    private function generateRekomendasi($avgNilai, $alfaCount, $trend)
    {
        $rekom = [];
        if ($avgNilai < 65) {
            $rekom[] = '🔄 Perlu bimbingan belajar intensif';
        } elseif ($avgNilai < 75) {
            $rekom[] = '📚 Tingkatkan latihan soal';
        }
        if ($trend === 'menurun') {
            $rekom[] = '⚠️ Nilai cenderung menurun, evaluasi metode belajar';
        }
        if ($alfaCount > 5) {
            $rekom[] = '📌 Tingkatkan kedisiplinan kehadiran';
        } elseif ($alfaCount > 2) {
            $rekom[] = '🔔 Pantau kehadiran lebih ketat';
        }
        if (empty($rekom)) {
            $rekom[] = '✅ Performa cukup baik, pertahankan';
        }
        return implode("\n", $rekom);
    }

    /**
     * API: Rekomendasi nilai saat input (return JSON)
     */
    public function rekomendasiNilai(Request $request)
    {
        $classSubjectId = $request->class_subject_id;
        $semesterId = $this->activeSemesterId();

        if (!$classSubjectId) {
            return response()->json(['success' => false]);
        }

        $grades = Grade::where('class_subject_id', $classSubjectId)
            ->where('semester_id', $semesterId)
            ->whereNotNull('nilai');

        return response()->json([
            'success' => true,
            'rata_kelas' => round($grades->avg('nilai'), 1),
            'tertinggi'  => round($grades->max('nilai'), 1),
            'terendah'   => round($grades->min('nilai'), 1),
            'total'      => $grades->count(),
        ]);
    }

    /**
     * Prediksi & Klasifikasi Siswa — halaman khusus
     */
    public function prediksi()
    {
        $user = auth()->user();
        $schoolId = $this->schoolId();
        $semesterId = $this->activeSemesterId();

        $classQuery = SchoolClass::where('school_id', $schoolId)->where('is_active', true);
        if ($user->role === 'guru') {
            $taughtIds = ClassSubject::where('teacher_id', $user->id)->pluck('class_id')->unique();
            $classQuery->whereIn('id', $taughtIds);
        }
        $classes = $classQuery->orderBy('tingkat')->orderBy('code')->get();

        $klasifikasi = collect();
        foreach ($classes as $class) {
            $students = Student::where('class_id', $class->id)->where('status', 'aktif')->withCount([
                'attendances as alfa_count' => fn($q) => $q->where('semester_id', $semesterId)
                    ->whereIn('status', ['alfa','tidak_hadir']),
                'grades as nilai_count' => fn($q) => $q->where('semester_id', $semesterId)->whereNotNull('nilai'),
            ])->get();

            foreach ($students as $student) {
                $avg = Grade::where('student_id', $student->id)
                    ->where('semester_id', $semesterId)
                    ->whereNotNull('nilai')->avg('nilai') ?? 0;

                $trend = $this->hitungTrendNilai($student->id, $semesterId);
                $alfaPct = $student->attendances()->where('semester_id', $semesterId)->count();
                $alfaPct = $alfaPct > 0 ? ($student->alfa_count / $alfaPct) * 100 : 0;

                // Klasifikasi AI
                $kategori = $this->klasifikasiSiswa($avg, $trend, $alfaPct);

                $klasifikasi->push((object)[
                    'nama'       => $student->nama_lengkap,
                    'kelas'      => $class->code,
                    'avg_nilai'  => round($avg, 1),
                    'alfa'       => $student->alfa_count,
                    'trend'      => $trend,
                    'kategori'   => $kategori['label'],
                    'warna'      => $kategori['warna'],
                    'deskripsi'  => $kategori['deskripsi'],
                    'rekomendasi' => $kategori['rekomendasi'],
                ]);
            }
        }

        $ringkasan = [
            'total'      => $klasifikasi->count(),
            'sangat_baik' => $klasifikasi->where('kategori', 'Sangat Baik')->count(),
            'baik'      => $klasifikasi->where('kategori', 'Baik')->count(),
            'cukup'     => $klasifikasi->where('kategori', 'Cukup')->count(),
            'perlu_bimbingan' => $klasifikasi->where('kategori', 'Perlu Bimbingan')->count(),
            'berisiko'   => $klasifikasi->where('kategori', 'Berisiko')->count(),
        ];

        return view('backend.ai.prediksi', compact('klasifikasi', 'ringkasan'));
    }

    /**
     * Auto-generate deskripsi rapor berdasarkan nilai
     */
    public function generateDeskripsi(Request $request)
    {
        $nilai = (float) ($request->nilai ?? 0);
        $mapel = $request->mapel ?? '';
        $tuntas = (bool) ($request->tuntas ?? true);
        $trend = $request->trend ?? 'stabil';

        $deskripsi = $this->buildDeskripsiRapor($nilai, $mapel, $tuntas, $trend);

        return response()->json([
            'success'   => true,
            'deskripsi' => $deskripsi,
            'nilai'     => $nilai,
        ]);
    }

    /**
     * Chatbot Pencarian Cerdas
     */
    public function search(Request $request)
    {
        $q = $request->q;
        if (!$q || strlen($q) < 2) {
            return response()->json(['success' => false, 'message' => 'Masukkan minimal 2 karakter.']);
        }

        $schoolId = $this->schoolId();
        $semesterId = $this->activeSemesterId();
        $results = [];

        // Cari siswa
        $siswa = Student::where('school_id', $schoolId)->where('status', 'aktif')
            ->where(fn($sq) => $sq->where('nama_lengkap', 'like', "%$q%")->orWhere('nis', 'like', "%$q%"))
            ->with('class')->limit(5)->get();

        foreach ($siswa as $s) {
            $avg = Grade::where('student_id', $s->id)->where('semester_id', $semesterId)
                ->whereNotNull('nilai')->avg('nilai');
            $results[] = [
                'type' => 'siswa',
                'icon' => 'graduation-cap',
                'title' => $s->nama_lengkap,
                'subtitle' => 'NIS: ' . ($s->nis ?? '-') . ' · Kelas: ' . ($s->class?->code ?? '-'),
                'detail' => $avg ? 'Rata-rata nilai: ' . round($avg, 1) : 'Belum ada nilai',
                'link' => url('/backend/master/students?search=' . urlencode($s->nama_lengkap)),
            ];
        }

        // Cari guru
        $guru = \App\Models\User::where('school_id', $schoolId)->where('is_active', true)
            ->whereIn('role', ['guru','walikelas','kepsek','bendahara'])
            ->where('name', 'like', "%$q%")->limit(3)->get();

        foreach ($guru as $g) {
            $results[] = [
                'type' => 'guru',
                'icon' => 'briefcase',
                'title' => $g->name,
                'subtitle' => ucwords(str_replace('_', ' ', $g->role)) . ' · ' . ($g->email ?? '-'),
                'detail' => $g->nip ? 'NIP: ' . $g->nip : '',
                'link' => url('/backend/master/teachers?search=' . urlencode($g->name)),
            ];
        }

        // Cari mapel
        $mapel = Subject::where('school_id', $schoolId)->where('is_active', true)
            ->where(fn($sq) => $sq->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"))
            ->limit(3)->get();

        foreach ($mapel as $m) {
            $avg = Grade::whereHas('classSubject', fn($csq) => $csq->where('subject_id', $m->id))
                ->where('semester_id', $semesterId)->whereNotNull('nilai')->avg('nilai');
            $results[] = [
                'type' => 'mapel',
                'icon' => 'book-open',
                'title' => $m->name,
                'subtitle' => 'Kode: ' . $m->code,
                'detail' => $avg ? 'Rata-rata nilai: ' . round($avg, 1) : 'Belum ada data',
                'link' => url('/backend/academic/curriculum'),
            ];
        }

        // Cari kelas
        $kelas = SchoolClass::where('school_id', $schoolId)->where('is_active', true)
            ->where(fn($sq) => $sq->where('code', 'like', "%$q%")->orWhere('name', 'like', "%$q%"))
            ->limit(3)->get();

        foreach ($kelas as $k) {
            $count = Student::where('class_id', $k->id)->where('status', 'aktif')->count();
            $results[] = [
                'type' => 'kelas',
                'icon' => 'school',
                'title' => $k->code . ' - ' . $k->name,
                'subtitle' => 'Tingkat: ' . $k->tingkat,
                'detail' => $count . ' siswa aktif',
                'link' => url('/backend/master/students?class_id=' . $k->id),
            ];
        }

        return response()->json([
            'success' => true,
            'query'   => $q,
            'total'   => count($results),
            'results' => $results,
        ]);
    }

    // ─── Helper Methods ───────────────────────────────────────────

    private function klasifikasiSiswa($avgNilai, $trend, $alfaPct)
    {
        if ($avgNilai >= 85 && $trend !== 'menurun' && $alfaPct <= 5) {
            return [
                'label' => 'Sangat Baik',
                'warna' => 'emerald',
                'deskripsi' => 'Performa akademik sangat baik dengan kehadiran optimal.',
                'rekomendasi' => 'Pertahankan prestasi dan ikuti program pengayaan.',
            ];
        }
        if ($avgNilai >= 75 && $trend !== 'menurun' && $alfaPct <= 10) {
            return [
                'label' => 'Baik',
                'warna' => 'blue',
                'deskripsi' => 'Performa akademik baik, masih bisa ditingkatkan.',
                'rekomendasi' => 'Tingkatkan latihan mandiri untuk hasil maksimal.',
            ];
        }
        if ($avgNilai >= 65 && $trend !== 'menurun') {
            return [
                'label' => 'Cukup',
                'warna' => 'amber',
                'deskripsi' => 'Performa cukup, perlu peningkatan di beberapa aspek.',
                'rekomendasi' => 'Fokus pada mapel dengan nilai di bawah KKM.',
            ];
        }
        if ($avgNilai < 65 && ($trend !== 'menurun' || $alfaPct <= 15)) {
            return [
                'label' => 'Perlu Bimbingan',
                'warna' => 'orange',
                'deskripsi' => 'Nilai di bawah KKM, memerlukan bimbingan tambahan.',
                'rekomendasi' => 'Ikuti program remedial dan bimbingan belajar.',
            ];
        }
        return [
            'label' => 'Berisiko',
            'warna' => 'red',
            'deskripsi' => 'Nilai rendah dan/atau tren menurun dengan kehadiran buruk.',
            'rekomendasi' => 'Perlu intervensi segera: konseling, remedial intensif, komunikasi orang tua.',
        ];
    }

    private function buildDeskripsiRapor($nilai, $mapel, $tuntas, $trend)
    {
        // Templates cerdas berdasarkan kategori nilai
        if ($nilai >= 90) {
            $templates = [
                "Ananda menunjukkan penguasaan yang sangat baik dalam mata pelajaran $mapel. Pemahaman konsep sangat kuat dan mampu menerapkan dalam berbagai konteks.",
                "Hasil belajar Ananda pada mata pelajaran $mapel sangat memuaskan. Mampu menganalisis dan menyelesaikan permasalahan dengan sangat baik.",
                "Ananda telah mencapai kompetensi dengan sangat baik pada $mapel. Kemampuan berpikir kritis dan pemecahan masalah sangat terasah.",
            ];
        } elseif ($nilai >= 80) {
            $templates = [
                "Ananda telah menguasai sebagian besar kompetensi pada mata pelajaran $mapel dengan baik. Perlu ditingkatkan pada aspek analisis dan penerapan.",
                "Penguasaan materi $mapel oleh Ananda sudah baik. Dapat melanjutkan ke materi berikutnya dengan sedikit penguatan.",
                "Ananda menunjukkan pemahaman yang baik dalam $mapel. Disarankan untuk lebih banyak berlatih soal-soal aplikatif.",
            ];
        } elseif ($nilai >= 70) {
            $templates = [
                "Ananda cukup menguasai kompetensi dasar pada mata pelajaran $mapel. Masih perlu bimbingan pada beberapa konsep penting.",
                "Pemahaman Ananda terhadap materi $mapel masih perlu ditingkatkan, terutama pada konsep-konsep yang lebih kompleks.",
                "Ananda telah menunjukkan kemajuan dalam $mapel. Disarankan mengikuti remedial pada topik yang belum tuntas.",
            ];
        } else {
            $templates = [
                "Ananda masih memerlukan bimbingan intensif dalam mata pelajaran $mapel. Beberapa kompetensi dasar belum tercapai.",
                "Penguasaan materi $mapel oleh Ananda masih di bawah standar. Perlu remedial dan pendampingan khusus.",
                "Ananda perlu mengulang beberapa materi pokok pada $mapel. Disarankan mengikuti program remedial dan bimbingan tambahan.",
            ];
        }

        $deskripsi = $templates[array_rand($templates)];

        // Tambahkan catatan tren
        if ($trend === 'menurun') {
            $deskripsi .= ' Perlu perhatian karena menunjukkan tren menurun.';
        } elseif ($trend === 'naik') {
            $deskripsi .= ' Menunjukkan perkembangan positif.';
        }

        // Catatan ketuntasan
        if (!$tuntas) {
            $deskripsi .= ' Beberapa TP/KD belum mencapai ketuntasan dan perlu diremedial.';
        }

        return $deskripsi;
    }
}
