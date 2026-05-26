<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSession;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LearningObjectiveSubject;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Report;
use App\Models\Semester;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class P5_PortalDataSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::where('is_active', true)->first();
        if (!$semester) {
            $this->command->warn('Tidak ada semester aktif. Lewati P5_PortalDataSeeder.');
            return;
        }

        $ta = AcademicYear::where('is_active', true)->first();
        if (!$ta) {
            $this->command->warn('Tidak ada tahun ajaran aktif. Lewati P5_PortalDataSeeder.');
            return;
        }

        $students = Student::with('class')->get();

        // ─── Isi phone untuk siswa yang belum punya ───
        $phonePrefixes = ['0812', '0813', '0856', '0878', '0896', '0821'];
        foreach ($students as $student) {
            if (empty($student->phone)) {
                $student->phone = $phonePrefixes[array_rand($phonePrefixes)] . rand(10000000, 99999999);
                $student->save();
            }
        }
        $students = $students->fresh();
        if ($students->isEmpty()) {
            $this->command->warn('Tidak ada data siswa. Lewati P5_PortalDataSeeder.');
            return;
        }

        $classSubjects = ClassSubject::with(['subject', 'teacher'])->get();
        if ($classSubjects->isEmpty()) {
            $this->command->warn('Tidak ada data class_subject. Lewati P5_PortalDataSeeder.');
            return;
        }

        $feeTypes = FeeType::all();
        $paymentMethods = PaymentMethod::all();

        // Ambil user guru untuk created_by
        $guruUser = User::where('role', 'guru')->first();
        $walikelasUser = User::where('role', 'walikelas')->first();
        $adminUser = User::where('role', 'admin')->first();
        $createdBy = $guruUser?->id ?? $walikelasUser?->id ?? $adminUser?->id ?? User::first()?->id;

        if (!$createdBy) {
            $this->command->warn('Tidak ada user. Lewati P5_PortalDataSeeder.');
            return;
        }

        // ─── Hapus data dari seeder sebelumnya (P3, P4) ───
        $this->command->info('Membersihkan data lama sebelum mengisi ulang...');
        $studentIds = $students->pluck('id')->toArray();
        $semesterId = $semester->id;

        $this->command->info('Grade before: ' . \Illuminate\Support\Facades\DB::table('grades')->whereIn('student_id', $studentIds)->where('semester_id', $semesterId)->count());

        // Hapus pakai DB::table agar tidak kena model events
        \Illuminate\Support\Facades\DB::table('grades')
            ->whereIn('student_id', $studentIds)
            ->where('semester_id', $semesterId)
            ->delete();

        $this->command->info('Grade after: ' . \Illuminate\Support\Facades\DB::table('grades')->whereIn('student_id', $studentIds)->where('semester_id', $semesterId)->count());

        \Illuminate\Support\Facades\DB::table('attendances')
            ->whereIn('student_id', $studentIds)
            ->where('semester_id', $semesterId)
            ->delete();

        \Illuminate\Support\Facades\DB::table('reports')
            ->whereIn('student_id', $studentIds)
            ->where('semester_id', $semesterId)
            ->delete();

        // Hapus exam results & sessions saja (exam dari P2 tetap dipertahankan)
        $examIds = \Illuminate\Support\Facades\DB::table('exams')
            ->where('semester_id', $semesterId)->pluck('id');

        \Illuminate\Support\Facades\DB::table('exam_results')
            ->whereIn('exam_id', $examIds)->delete();
        \Illuminate\Support\Facades\DB::table('exam_sessions')
            ->whereIn('exam_id', $examIds)->delete();

        // Hapus invoice & payment
        $allInvoiceIds = \Illuminate\Support\Facades\DB::table('invoices')->pluck('id');
        \Illuminate\Support\Facades\DB::table('payments')
            ->whereIn('invoice_id', $allInvoiceIds)->delete();
        \Illuminate\Support\Facades\DB::table('invoice_items')
            ->whereIn('invoice_id', $allInvoiceIds)->delete();
        \Illuminate\Support\Facades\DB::table('invoices')
            ->whereIn('id', $allInvoiceIds)->delete();

        // ─────────────────────────────────────────────────────────
        // 1. NILAI (Grade) per TP untuk setiap siswa
        // ─────────────────────────────────────────────────────────
        $this->command->info('Mengisi data nilai (Grade)...');
        $gradeBar = $this->command->getOutput()->createProgressBar($students->count() * $classSubjects->count());
        $gradeBar->start();

        foreach ($students as $student) {
            foreach ($classSubjects as $cs) {
                if ($cs->class_id !== $student->class_id) continue;

                $losList = LearningObjectiveSubject::where('class_subject_id', $cs->id)
                    ->where('semester_id', $semester->id)
                    ->get();

                if ($losList->isEmpty()) continue;

                foreach ($losList as $los) {
                    // Hanya 1 formatif dan 1 sumatif per TP (unique constraint)
                    Grade::create([
                        'student_id' => $student->id,
                        'class_subject_id' => $cs->id,
                        'semester_id' => $semester->id,
                        'learning_objective_id' => $los->learning_objective_id,
                        'jenis_nilai' => 'formatif',
                        'nilai' => rand(0, 2) === 0 ? rand(40, 69) : rand(70, 100),
                        'deskripsi' => 'Penilaian Formatif',
                        'created_by' => $createdBy,
                        'created_at' => now()->subDays(rand(1, 60)),
                    ]);

                    Grade::create([
                        'student_id' => $student->id,
                        'class_subject_id' => $cs->id,
                        'semester_id' => $semester->id,
                        'learning_objective_id' => $los->learning_objective_id,
                        'jenis_nilai' => 'sumatif',
                        'nilai' => rand(0, 2) === 0 ? rand(40, 69) : rand(70, 100),
                        'deskripsi' => 'Penilaian Sumatif',
                        'created_by' => $createdBy,
                        'created_at' => now()->subDays(rand(1, 60)),
                    ]);
                }
                $gradeBar->advance();
            }
        }
        $gradeBar->finish();
        $this->command->newLine();

        // ─────────────────────────────────────────────────────────
        // 2. PRESENSI (Attendance) harian — 60 hari terakhir
        // ─────────────────────────────────────────────────────────
        $this->command->info('Mengisi data presensi (Attendance)...');
        $statuses = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'terlambat', 'izin', 'sakit', 'alfa'];
        $attendanceBar = $this->command->getOutput()->createProgressBar($students->count() * 60);
        $attendanceBar->start();

        foreach ($students as $student) {
            $studentCS = $classSubjects->where('class_id', $student->class_id);

            for ($day = 1; $day <= 60; $day++) {
                $date = now()->subDays(60 - $day);
                if ($date->isWeekend()) continue;

                $dailySubjects = $studentCS->random(min($studentCS->count(), rand(2, 4)));
                foreach ($dailySubjects as $cs) {
                    $status = $statuses[array_rand($statuses)];
                    $keterangan = $status === 'alfa' ? 'Tidak hadir tanpa keterangan' :
                        ($status === 'sakit' ? 'Sakit' :
                        ($status === 'izin' ? 'Izin keluarga' : null));

                    Attendance::create([
                        'student_id' => $student->id,
                        'class_subject_id' => $cs->id,
                        'semester_id' => $semester->id,
                        'tanggal' => $date,
                        'status' => $status,
                        'keterangan' => $keterangan,
                        'created_by' => $createdBy,
                        'created_at' => $date->copy()->setHour(7)->setMinute(rand(0, 59)),
                    ]);
                }
                $attendanceBar->advance();
            }
        }
        $attendanceBar->finish();
        $this->command->newLine();

        // ─────────────────────────────────────────────────────────
        // 3. UJIAN & HASIL UJIAN (pakai exam dari P2)
        // ─────────────────────────────────────────────────────────
        $this->command->info('Mengisi data ujian & hasil...');

        $existingExams = Exam::with('examQuestions.question')
            ->where('semester_id', $semester->id)
            ->get();

        $examBar = $this->command->getOutput()->createProgressBar($existingExams->count());
        $examBar->start();

        foreach ($existingExams as $exam) {
            $startTime = $exam->start_time;
            $endTime = $exam->end_time;

            // Targetkan siswa berdasarkan class_ids ujian
            $examClassIds = is_array($exam->class_ids) ? $exam->class_ids : json_decode($exam->class_ids, true);
            $examStudents = $students->whereIn('class_id', $examClassIds);

            foreach ($examStudents as $student) {
                $score = rand(35, 100);
                $isPassed = $score >= 70;
                $totalQ = max($exam->total_questions, 1);

                $session = ExamSession::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'status' => 'finished',
                    'started_at' => $startTime,
                    'finished_at' => $endTime,
                ]);

                ExamResult::create([
                    'exam_session_id' => $session->id,
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'total_score' => $score,
                    'correct_count' => (int) round($score / 100 * $totalQ),
                    'wrong_count' => $totalQ - (int) round($score / 100 * $totalQ),
                    'is_passed' => $isPassed,
                    'graded_at' => $endTime,
                ]);
            }
            $examBar->advance();
        }
        $examBar->finish();

        // ─────────────────────────────────────────────────────────
        // 4. TAGIHAN (Invoice) & PEMBAYARAN
        // ─────────────────────────────────────────────────────────
        $this->command->info('Mengisi data tagihan & pembayaran...');

        if ($feeTypes->isNotEmpty()) {
            $invoiceBar = $this->command->getOutput()->createProgressBar($students->count());
            $invoiceBar->start();

            foreach ($students as $student) {
                $invoiceCount = rand(2, 4);
                for ($i = 0; $i < $invoiceCount; $i++) {
                    $feeType = $feeTypes->random();
                    $total = $feeType->amount ?? rand(100000, 500000);
                    $dueDate = now()->subDays(rand(1, 45))->addDays(rand(10, 30));
                    $isPaid = rand(0, 1);

                    $invoice = Invoice::create([
                        'school_id' => $student->school_id,
                        'student_id' => $student->id,
                        'academic_year_id' => $ta->id,
                        'semester' => $semester->semester ?? '1',
                        'invoice_number' => 'INV-' . date('ym') . '-' . str_pad(substr($student->id, -4), 4, '0', STR_PAD_LEFT) . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                        'subtotal' => $total,
                        'discount' => 0,
                        'total' => $total,
                        'status' => $isPaid ? 'paid' : (rand(0, 1) ? 'unpaid' : 'partial'),
                        'due_date' => $dueDate,
                        'paid_at' => $isPaid ? now()->subDays(rand(1, 30)) : null,
                        'notes' => null,
                        'created_by' => $createdBy,
                        'created_at' => now()->subDays(rand(30, 60)),
                    ]);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'fee_type_id' => $feeType->id,
                        'fee_name' => $feeType->name,
                        'description' => $feeType->description ?? 'Biaya ' . $feeType->name,
                        'quantity' => 1,
                        'unit_price' => $total,
                        'subtotal' => $total,
                    ]);

                    // Jika lunas, buat payment record
                    if ($isPaid) {
                        $paymentMethod = $paymentMethods->first() ?? null;
                        Payment::create([
                            'school_id' => $student->school_id,
                            'invoice_id' => $invoice->id,
                            'student_id' => $student->id,
                            'payment_method_id' => $paymentMethod?->id,
                            'payment_number' => 'PAY-' . date('ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                            'paid_by' => 'Siswa (Mandiri)',
                            'amount' => $total,
                            'admin_fee' => 0,
                            'payment_channel' => 'Transfer Bank',
                            'status' => 'verified',
                            'payment_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                            'verified_at' => now()->subDays(rand(1, 29)),
                            'notes' => 'Pembayaran via transfer',
                            'created_at' => now()->subDays(rand(30, 60)),
                        ]);
                    }
                }
                $invoiceBar->advance();
            }
            $invoiceBar->finish();
            $this->command->newLine();
        }

        // ─────────────────────────────────────────────────────────
        // 5. RAPOR (locked) untuk setiap siswa per mapel
        // ─────────────────────────────────────────────────────────
        $this->command->info('Mengisi data rapor...');

        foreach ($students as $student) {
            $studentCS = $classSubjects->where('class_id', $student->class_id);
            foreach ($studentCS as $cs) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('class_subject_id', $cs->id)
                    ->where('semester_id', $semester->id)
                    ->get();

                $nilaiList = $grades->pluck('nilai')->filter();
                $nilaiAkhir = $nilaiList->count() > 0 ? round($nilaiList->avg(), 2) : null;

                $predikat = null;
                if ($nilaiAkhir !== null) {
                    $predikat = match (true) {
                        $nilaiAkhir >= 90 => 'A',
                        $nilaiAkhir >= 80 => 'B',
                        $nilaiAkhir >= 70 => 'C',
                        $nilaiAkhir >= 60 => 'D',
                        default => 'E',
                    };
                }

                Report::create([
                    'student_id' => $student->id,
                    'semester_id' => $semester->id,
                    'class_subject_id' => $cs->id,
                    'nilai_akhir' => $nilaiAkhir,
                    'predikat' => $predikat,
                    'deskripsi_cp' => $predikat ? "Pencapaian {$predikat} pada {$cs->subject->name}" : null,
                    'is_locked' => true,
                    'locked_at' => now()->subDays(rand(1, 10)),
                    'created_at' => now()->subDays(rand(10, 20)),
                ]);
            }
        }

        $this->command->info('Data portal berhasil diisi!');
    }
}
