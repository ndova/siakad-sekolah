<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\LearningObjective;
use App\Models\LearningObjectiveSubject;
use App\Models\LearningOutcome;
use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class P2_AcademicsSeeder extends Seeder
{
    public static array $learningObjectives = [], $questionBanks = [];

    public function run(): void
    {
        $schoolId = P1_CoreSeeder::$school['id'];
        $taId = P1_CoreSeeder::$ta['id'];
        $semId = P1_CoreSeeder::$semGanjil['id'];
        $teacherId = P1_CoreSeeder::$users['guru@siakad.test']['id'];

        $cur = Curriculum::create(['id' => '0199c000-5000-7000-8000-000000000001', 'school_id' => $schoolId, 'name' => 'Kurikulum Merdeka 2025/2026', 'academic_year_id' => $taId, 'is_active' => true]);

        $tpBase = [
            'BIN' => ['Menganalisis teks', 'Menulis esai', 'Mengidentifikasi struktur'],
            'MTK' => ['Menyelesaikan persamaan', 'Memahami fungsi kuadrat', 'Menerapkan trigonometri'],
            'ING' => ['Understanding narrative', 'Writing descriptive', 'Speaking presentation'],
        ];

        foreach (['BIN', 'MTK', 'ING'] as $code) {
            $cp = LearningOutcome::create([
                'id' => Str::uuid(), 'curriculum_id' => $cur->id,
                'subject_id' => P1_CoreSeeder::$subjects[$code]['id'],
                'phase' => 'E', 'code' => "CP-{$code}",
                'description' => "Capaian Pembelajaran {$code} Fase E", 'urutan' => 1,
            ]);

            foreach ($tpBase[$code] as $i => $desc) {
                $tpCode = "TP-{$code}-" . ($i + 1);
                $tp = LearningObjective::create([
                    'id' => Str::uuid(), 'learning_outcome_id' => $cp->id,
                    'code' => $tpCode, 'description' => $desc,
                    'level_kognitif' => ['C3', 'C4', 'C5'][$i], 'urutan' => $i + 1,
                ]);
                self::$learningObjectives[$tpCode] = $tp->toArray();

                $csKey = "X-IPA-1_{$code}";
                if (isset(P1_CoreSeeder::$classSubjects[$csKey])) {
                    LearningObjectiveSubject::create([
                        'id' => Str::uuid(), 'learning_objective_id' => $tp->id,
                        'class_subject_id' => P1_CoreSeeder::$classSubjects[$csKey]['id'],
                        'semester_id' => $semId, 'urutan_ajar' => $i + 1,
                    ]);
                }
            }
        }

        // Question Banks with rich realistic questions
        $soalData = [
            'BIN' => [
                ['type' => 'pg', 'content' => 'Bacalah teks berikut! "Hutan mangrove memiliki fungsi ekologis yang sangat penting. Selain menjadi habitat berbagai spesies, hutan mangrove juga berperan dalam mencegah abrasi pantai." Teks tersebut termasuk jenis teks...', 'options' => ['A' => 'Anekdot', 'B' => 'Laporan Hasil Observasi', 'C' => 'Eksposisi', 'D' => 'Narasi', 'E' => 'Deskripsi'], 'answer_key' => 'B', 'score' => 10, 'level_kognitif' => 'C3', 'difficulty' => 'mudah'],
                ['type' => 'pg', 'content' => 'Cermati kalimat berikut: "Kemarin, saya melihat seorang pejabat yang begitu sederhana, rumahnya hanya seluas lapangan bola." Kalimat tersebut mengandung majas...', 'options' => ['A' => 'Metafora', 'B' => 'Personifikasi', 'C' => 'Ironi', 'D' => 'Hiperbola', 'E' => 'Litotes'], 'answer_key' => 'C', 'score' => 10, 'level_kognitif' => 'C3', 'difficulty' => 'sedang'],
                ['type' => 'pg', 'content' => 'Struktur teks anekdot yang benar secara berurutan adalah...', 'options' => ['A' => 'Orientasi – Komplikasi – Evaluasi – Reaksi', 'B' => 'Abstrak – Orientasi – Krisis – Reaksi – Koda', 'C' => 'Tesis – Argumentasi – Penegasan Ulang', 'D' => 'Pernyataan Umum – Deskripsi Bagian – Simpulan', 'E' => 'Abstrak – Koda – Krisis – Reaksi'], 'answer_key' => 'B', 'score' => 10, 'level_kognitif' => 'C4', 'difficulty' => 'sedang'],
                ['type' => 'esai', 'content' => 'Jelaskan perbedaan antara fakta dan opini dalam teks eksposisi! Berikan masing-masing satu contoh kalimat.', 'options' => null, 'answer_key' => 'Fakta adalah pernyataan yang dapat dibuktikan kebenarannya berdasarkan data/kenyataan. Opini adalah pendapat pribadi yang belum tentu benar. Contoh fakta: "Indonesia memiliki 17.504 pulau." Contoh opini: "Menurut saya, kurikulum merdeka lebih baik dari kurikulum sebelumnya."', 'score' => 20, 'level_kognitif' => 'C4', 'difficulty' => 'sedang'],
                ['type' => 'esai', 'content' => 'Tulislah sebuah paragraf argumentatif (minimal 5 kalimat) bertema "Pentingnya literasi digital bagi pelajar di era modern"!', 'options' => null, 'answer_key' => 'Paragraf harus memiliki: (1) kalimat utama yang jelas, (2) argumen pendukung minimal 3, (3) data/fakta penguat, (4) koherensi antar kalimat, (5) simpulan singkat.', 'score' => 20, 'level_kognitif' => 'C5', 'difficulty' => 'sulit'],
            ],
            'MTK' => [
                ['type' => 'pg', 'content' => 'Jika 2x + 5 = 17, maka nilai x adalah...', 'options' => ['A' => '5', 'B' => '6', 'C' => '7', 'D' => '8', 'E' => '11'], 'answer_key' => 'B', 'score' => 10, 'level_kognitif' => 'C3', 'difficulty' => 'mudah'],
                ['type' => 'pg', 'content' => 'Fungsi kuadrat f(x) = x² – 4x + 3 memiliki titik potong sumbu X di...', 'options' => ['A' => '(1,0) dan (3,0)', 'B' => '(0,1) dan (0,3)', 'C' => '(-1,0) dan (-3,0)', 'D' => '(2,0) saja', 'E' => '(4,0) saja'], 'answer_key' => 'A', 'score' => 10, 'level_kognitif' => 'C3', 'difficulty' => 'sedang'],
                ['type' => 'pg', 'content' => 'Diketahui segitiga siku-siku ABC dengan sudut A = 30° dan sisi miring = 10 cm. Panjang sisi depan sudut A adalah...', 'options' => ['A' => '5 cm', 'B' => '5√3 cm', 'C' => '10√3 cm', 'D' => '20 cm', 'E' => '8.66 cm'], 'answer_key' => 'A', 'score' => 10, 'level_kognitif' => 'C4', 'difficulty' => 'sedang'],
                ['type' => 'esai', 'content' => 'Diketahui fungsi f(x) = x² – 2x – 8. Tentukan: a) Titik potong sumbu X dan Y, b) Sumbu simetri, c) Titik puncak, d) Gambarkan sketsa grafiknya!', 'options' => null, 'answer_key' => 'a) Titik potong X: (-2,0) dan (4,0); titik potong Y: (0,-8). b) Sumbu simetri: x = 1. c) Titik puncak: (1, -9). d) Kurva parabola terbuka ke atas.', 'score' => 20, 'level_kognitif' => 'C5', 'difficulty' => 'sulit'],
                ['type' => 'esai', 'content' => 'Sebuah tangga sepanjang 5 meter disandarkan pada dinding. Jarak kaki tangga ke dinding 3 meter. Tentukan: a) Tinggi dinding yang dicapai tangga, b) Sudut antara tangga dan tanah!', 'options' => null, 'answer_key' => 'a) 4 meter (menggunakan Pythagoras: √(5²-3²) = √16 = 4). b) sin θ = 4/5, maka θ = 53.13° (atau gunakan tan θ = 4/3).', 'score' => 20, 'level_kognitif' => 'C5', 'difficulty' => 'sulit'],
            ],
            'ING' => [
                ['type' => 'pg', 'content' => 'Read the text: "Once upon a time, there was a beautiful princess who lived in a castle. One day, an evil witch cursed her to sleep forever." This text is a...', 'options' => ['A' => 'Recount Text', 'B' => 'Descriptive Text', 'C' => 'Narrative Text', 'D' => 'Procedure Text', 'E' => 'Report Text'], 'answer_key' => 'C', 'score' => 10, 'level_kognitif' => 'C3', 'difficulty' => 'mudah'],
                ['type' => 'pg', 'content' => '"My classroom is big and clean. There are 36 desks, a whiteboard, and two fans. The walls are decorated with students\' artwork." The text describes...', 'options' => ['A' => 'A person', 'B' => 'A place', 'C' => 'An animal', 'D' => 'An event', 'E' => 'A procedure'], 'answer_key' => 'B', 'score' => 10, 'level_kognitif' => 'C3', 'difficulty' => 'mudah'],
                ['type' => 'pg', 'content' => 'Complete: "If I ___ rich, I would travel around the world."', 'options' => ['A' => 'am', 'B' => 'was', 'C' => 'were', 'D' => 'be', 'E' => 'being'], 'answer_key' => 'C', 'score' => 10, 'level_kognitif' => 'C4', 'difficulty' => 'sedang'],
                ['type' => 'esai', 'content' => 'Write a descriptive paragraph about your favorite place (min. 5 sentences). Use adjectives and sensory details (sight, sound, smell).', 'options' => null, 'answer_key' => 'Paragraph must include: (1) topic sentence introducing the place, (2) at least 3 sensory details, (3) proper use of adjectives, (4) correct grammar and spelling, (5) concluding sentence.', 'score' => 20, 'level_kognitif' => 'C5', 'difficulty' => 'sulit'],
                ['type' => 'esai', 'content' => 'Read: "The boy who won the competition is my brother." Analyze: a) What is the main clause? b) What is the relative clause? c) What is the function of "who"?', 'options' => null, 'answer_key' => 'a) Main clause: "The boy is my brother." b) Relative clause: "who won the competition." c) "Who" functions as a relative pronoun modifying "the boy" (subject of the relative clause).', 'score' => 20, 'level_kognitif' => 'C4', 'difficulty' => 'sedang'],
            ],
        ];

        foreach (['BIN', 'MTK', 'ING'] as $code) {
            $bank = QuestionBank::create([
                'id' => Str::uuid(), 'school_id' => $schoolId,
                'name' => "Bank Soal " . P1_CoreSeeder::$subjects[$code]['name'],
                'subject_id' => P1_CoreSeeder::$subjects[$code]['id'],
                'class_id' => P1_CoreSeeder::$classes['X-IPA-1']['id'],
                'created_by' => $teacherId, 'is_shared' => true,
            ]);
            self::$questionBanks[$code] = $bank->toArray();

            foreach ($soalData[$code] as $i => $soal) {
                $tpId = self::$learningObjectives["TP-{$code}-" . min($i + 1, 3)]['id'] ?? null;

                Question::create([
                    'id' => Str::uuid(), 'question_bank_id' => $bank->id,
                    'learning_objective_id' => $tpId,
                    'type' => $soal['type'],
                    'content' => $soal['content'],
                    'options' => $soal['options'],
                    'answer_key' => $soal['answer_key'],
                    'score' => $soal['score'],
                    'level_kognitif' => $soal['level_kognitif'],
                    'difficulty' => $soal['difficulty'],
                    'created_by' => $teacherId,
                ]);
            }
        }

        // Exams
        $exams = [
            ['title' => 'UH 1 - Bahasa Indonesia', 'type' => 'uh', 'subj' => 'BIN', 'kls' => 'X-IPA-1', 'dur' => 90],
            ['title' => 'STS Matematika', 'type' => 'sts', 'subj' => 'MTK', 'kls' => 'X-IPA-1', 'dur' => 120],
        ];

        foreach ($exams as $ed) {
            $exam = Exam::create([
                'id' => Str::uuid(), 'school_id' => $schoolId,
                'code' => 'EX-' . strtoupper(Str::random(8)), 'title' => $ed['title'],
                'type' => $ed['type'], 'subject_id' => P1_CoreSeeder::$subjects[$ed['subj']]['id'],
                'class_ids' => [P1_CoreSeeder::$classes[$ed['kls']]['id']],
                'semester_id' => $semId,
                'start_time' => now()->addDays(3)->setTime(8, 0),
                'end_time' => now()->addDays(3)->addMinutes($ed['dur']),
                'duration' => $ed['dur'], 'total_questions' => 0, 'total_score' => 0,
                'random_questions' => false, 'random_answers' => false,
                'show_result' => true, 'max_devices' => 1,
                'status' => 'published', 'created_by' => $teacherId,
            ]);

            $qs = Question::where('question_bank_id', self::$questionBanks[$ed['subj']]['id'])
                ->orderBy('type')->orderBy('score')->get();
            $ts = 0;
            foreach ($qs as $i => $q) {
                ExamQuestion::create(['id' => Str::uuid(), 'exam_id' => $exam->id, 'question_id' => $q->id, 'urutan' => $i + 1, 'score_override' => null]);
                $ts += $q->score;
            }
            $exam->update(['total_questions' => $qs->count(), 'total_score' => $ts]);
        }

        echo "✅ Academics seeded (curriculum, CP, TP, banks, questions, exams)\n";
    }
}
