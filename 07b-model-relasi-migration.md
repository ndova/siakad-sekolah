# Database Terpadu SIAKAD — Model Laravel, Relasi & Migration

> **Lanjutan dari**: `07a-database-terpadu.md` (36 tabel + field kunci)

---

## 1. Daftar Model Laravel

```
app/Models/
├── School.php
├── AcademicYear.php
├── Semester.php
├── Major.php                    // Jurusan SMK
├── SchoolClass.php              // Rombel (hindari reserved "Class")
├── Subject.php
├── ClassSubject.php             // Pivot mapel di kelas
├── User.php
├── Student.php
├── Parent.php                   // reserved word → ParentModel? pakai Guardian? 
│                                // solusi: pakai namespace App\Models\Parent
├── ParentStudent.php            // Pivot parent_student
├── Curriculum.php
├── LearningOutcome.php          // CP
├── LearningObjective.php        // TP
├── LearningObjectiveSubject.php // ATP mapping
├── Grade.php
├── Report.php
├── P5Project.php
├── P5Assessment.php
├── Attendance.php
├── QuestionBank.php
├── Question.php
├── Exam.php
├── ExamQuestion.php             // Pivot exam ↔ question
├── ExamSession.php
├── ExamAnswer.php
├── ExamResult.php
├── FeeType.php
├── FeeTypeTarget.php
├── Invoice.php
├── InvoiceItem.php
├── PaymentMethod.php
├── Payment.php
├── PaymentLog.php
├── StudentClassHistory.php
├── Notification.php
```

> **Catatan**: Di PHP, `class Parent` adalah reserved syntax error. Solusi: model bernama `Guardian.php` (tabel `parents`) atau gunakan nama model `StudentParent.php`.

---

## 2. Relasi Antar Model

### 2.1 Blok Master

```php
// School.php
class School extends Model
{
    public function academicYears(): HasMany { return $this->hasMany(AcademicYear::class); }
    public function majors(): HasMany { return $this->hasMany(Major::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
    public function feeTypes(): HasMany { return $this->hasMany(FeeType::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
}

// AcademicYear.php
class AcademicYear extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function semesters(): HasMany { return $this->hasMany(Semester::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function curriculum(): HasOne { return $this->hasOne(Curriculum::class); }
}

// Semester.php
class Semester extends Model
{
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
    public function p5Projects(): HasMany { return $this->hasMany(P5Project::class); }
}

// Major.php
class Major extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
}

// SchoolClass.php (Rombel)
class SchoolClass extends Model
{
    protected $table = 'classes';

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function waliKelas(): BelongsTo { return $this->belongsTo(User::class, 'wali_kelas_id'); }
    public function classSubjects(): HasMany { return $this->hasMany(ClassSubject::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
}

// Subject.php
class Subject extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classSubjects(): HasMany { return $this->hasMany(ClassSubject::class); }
    public function learningOutcomes(): HasMany { return $this->hasMany(LearningOutcome::class); }
    public function questionBanks(): HasMany { return $this->hasMany(QuestionBank::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
}

// ClassSubject.php (Pivot mapel di kelas + guru pengampu)
class ClassSubject extends Model
{
    protected $table = 'class_subject';

    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function learningObjectiveSubjects(): HasMany { return $this->hasMany(LearningObjectiveSubject::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
}
```

### 2.2 Blok User, Siswa & Orang Tua

```php
// User.php
class User extends Authenticatable
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }

    // Role-based: jika role='siswa'
    public function student(): HasOne { return $this->hasOne(Student::class); }

    // Role-based: jika role='orang_tua'
    public function guardian(): HasOne { return $this->hasOne(Guardian::class); }

    // Role-based: jika role='guru','walikelas'
    public function classSubjectsAsTeacher(): HasMany { return $this->hasMany(ClassSubject::class, 'teacher_id'); }
    public function homeroomClass(): HasOne { return $this->hasOne(SchoolClass::class, 'wali_kelas_id'); }

    public function createdGrades(): HasMany { return $this->hasMany(Grade::class, 'created_by'); }
    public function createdQuestions(): HasMany { return $this->hasMany(Question::class, 'created_by'); }
    public function createdExams(): HasMany { return $this->hasMany(Exam::class, 'created_by'); }
    public function verifiedPayments(): HasMany { return $this->hasMany(Payment::class, 'verified_by'); }
    public function createdInvoices(): HasMany { return $this->hasMany(Invoice::class, 'created_by'); }
    public function notifications(): HasMany { return $this->hasMany(Notification::class); }
}

// Student.php
class Student extends Model
{
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function class(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function parents(): BelongsToMany { return $this->belongsToMany(Guardian::class, 'parent_student', 'student_id', 'parent_id'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function reports(): HasMany { return $this->hasMany(Report::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function p5Assessments(): HasMany { return $this->hasMany(P5Assessment::class); }
    public function examSessions(): HasMany { return $this->hasMany(ExamSession::class); }
    public function examResults(): HasMany { return $this->hasMany(ExamResult::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function classHistory(): HasMany { return $this->hasMany(StudentClassHistory::class); }
}

// Guardian.php (tabel: parents)
class Guardian extends Model
{
    protected $table = 'parents';

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function students(): BelongsToMany { return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id'); }

    // Proxy untuk akses data anak
    public function getChildInvoicesAttribute() {
        return Invoice::whereIn('student_id', $this->students()->pluck('students.id'));
    }
    public function getChildPaymentsAttribute() {
        return Payment::whereIn('student_id', $this->students()->pluck('students.id'));
    }
}
```

### 2.3 Blok Kurikulum

```php
// Curriculum.php
class Curriculum extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function learningOutcomes(): HasMany { return $this->hasMany(LearningOutcome::class); }
}

// LearningOutcome.php (CP)
class LearningOutcome extends Model
{
    protected $table = 'learning_outcomes';

    public function curriculum(): BelongsTo { return $this->belongsTo(Curriculum::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function learningObjectives(): HasMany { return $this->hasMany(LearningObjective::class); }
}

// LearningObjective.php (TP)
class LearningObjective extends Model
{
    protected $table = 'learning_objectives';

    public function learningOutcome(): BelongsTo { return $this->belongsTo(LearningOutcome::class); }
    public function learningObjectiveSubjects(): HasMany { return $this->hasMany(LearningObjectiveSubject::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function questions(): HasMany { return $this->hasMany(Question::class); }
}

// LearningObjectiveSubject.php (ATP Mapping)
class LearningObjectiveSubject extends Model
{
    protected $table = 'learning_objective_subjects';

    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
}
```

### 2.4 Blok Nilai, Rapor, P5, Presensi

```php
// Grade.php
class Grade extends Model
{
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function examResult(): BelongsTo { return $this->belongsTo(ExamResult::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}

// Report.php
class Report extends Model
{
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function locker(): BelongsTo { return $this->belongsTo(User::class, 'locked_by'); }
}

// P5Project.php
class P5Project extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function assessments(): HasMany { return $this->hasMany(P5Assessment::class); }
}

// P5Assessment.php
class P5Assessment extends Model
{
    public function p5Project(): BelongsTo { return $this->belongsTo(P5Project::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}

// Attendance.php
class Attendance extends Model
{
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function classSubject(): BelongsTo { return $this->belongsTo(ClassSubject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
```

### 2.5 Blok Exam

```php
// QuestionBank.php
class QuestionBank extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function questions(): HasMany { return $this->hasMany(Question::class); }
}

// Question.php
class Question extends Model
{
    public function questionBank(): BelongsTo { return $this->belongsTo(QuestionBank::class); }
    public function learningObjective(): BelongsTo { return $this->belongsTo(LearningObjective::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }

    protected function casts(): array {
        return ['options' => 'array', 'media' => 'array'];
    }
}

// Exam.php
class Exam extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function examQuestions(): HasMany { return $this->hasMany(ExamQuestion::class); }
    public function sessions(): HasMany { return $this->hasMany(ExamSession::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }

    protected function casts(): array {
        return ['class_ids' => 'array'];
    }
}

// ExamQuestion.php (Pivot)
class ExamQuestion extends Model
{
    protected $table = 'exam_questions';

    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
    public function answers(): HasMany { return $this->hasMany(ExamAnswer::class); }
}

// ExamSession.php
class ExamSession extends Model
{
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function answers(): HasMany { return $this->hasMany(ExamAnswer::class); }
    public function result(): HasOne { return $this->hasOne(ExamResult::class); }
}

// ExamAnswer.php
class ExamAnswer extends Model
{
    public function examSession(): BelongsTo { return $this->belongsTo(ExamSession::class); }
    public function examQuestion(): BelongsTo { return $this->belongsTo(ExamQuestion::class); }

    protected function casts(): array {
        return ['selected_options' => 'array'];
    }
}

// ExamResult.php
class ExamResult extends Model
{
    public function examSession(): BelongsTo { return $this->belongsTo(ExamSession::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function grader(): BelongsTo { return $this->belongsTo(User::class, 'graded_by'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }

    protected function casts(): array {
        return ['tp_scores' => 'array'];
    }
}
```

### 2.6 Blok Pembayaran

```php
// FeeType.php
class FeeType extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function targets(): HasMany { return $this->hasMany(FeeTypeTarget::class); }
    public function invoiceItems(): HasMany { return $this->hasMany(InvoiceItem::class); }
}

// FeeTypeTarget.php
class FeeTypeTarget extends Model
{
    public function feeType(): BelongsTo { return $this->belongsTo(FeeType::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class, 'jurusan_id'); }
}

// Invoice.php
class Invoice extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    // Computed: total terbayar
    public function getPaidAmountAttribute(): float {
        return $this->payments()->where('status', 'verified')->sum('amount');
    }
    public function getRemainingAttribute(): float {
        return max(0, $this->total - $this->paid_amount);
    }
}

// InvoiceItem.php
class InvoiceItem extends Model
{
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function feeType(): BelongsTo { return $this->belongsTo(FeeType::class); }
}

// PaymentMethod.php
class PaymentMethod extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}

// Payment.php
class Payment extends Model
{
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function logs(): HasMany { return $this->hasMany(PaymentLog::class); }
}

// PaymentLog.php
class PaymentLog extends Model
{
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
```

---

## 3. Contoh Migration untuk Tabel Kunci

### 3.1 Migration `students`

```php
// database/migrations/xxxx_xx_xx_create_students_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->nullOnDelete();

            $table->string('nisn', 10)->unique();
            $table->string('nis', 20);
            $table->string('nama_lengkap', 200);
            $table->char('jk', 1);  // L / P
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ayah', 200)->nullable();
            $table->string('nama_ibu', 200)->nullable();
            $table->string('status', 20)->default('aktif');
                // aktif, lulus, pindah, keluar
            $table->date('tanggal_masuk')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
```

### 3.2 Migration `parents` + Pivot

```php
// create_parents_table.php
Schema::create('parents', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
    $table->string('nama_lengkap', 200);
    $table->char('jk', 1);
    $table->string('hubungan', 20);  // ayah, ibu, wali
    $table->string('pekerjaan', 100)->nullable();
    $table->string('phone', 20)->nullable();
    $table->text('alamat')->nullable();
    $table->timestamps();
});

// create_parent_student_table.php
Schema::create('parent_student', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('parent_id')->constrained('parents')->cascadeOnDelete();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    $table->boolean('is_primary')->default(false);
    $table->unique(['parent_id', 'student_id']);
});
```

### 3.3 Migration `exams` + `exam_questions` + `exam_sessions`

```php
// create_exams_table.php
Schema::create('exams', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
    $table->string('code', 30);
    $table->string('title', 200);
    $table->string('type', 20);  // uh, sts, sas, asaj, tryout
    $table->foreignUuid('subject_id')->constrained('subjects');
    $table->jsonb('class_ids');  // [UUID kelas]
    $table->foreignUuid('semester_id')->constrained('semesters');
    $table->timestamp('start_time');
    $table->timestamp('end_time');
    $table->integer('duration');  // menit
    $table->integer('total_questions')->default(0);
    $table->decimal('total_score', 6, 2)->default(0);
    $table->boolean('random_questions')->default(false);
    $table->boolean('random_answers')->default(false);
    $table->boolean('show_result')->default(false);
    $table->smallInteger('max_devices')->default(1);
    $table->string('status', 20)->default('draft');
    $table->foreignUuid('created_by')->constrained('users');
    $table->timestamps();
});

// create_exam_questions_table.php
Schema::create('exam_questions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
    $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
    $table->smallInteger('urutan');
    $table->decimal('score_override', 5, 2)->nullable();
    $table->unique(['exam_id', 'question_id']);
});

// create_exam_sessions_table.php
Schema::create('exam_sessions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('finished_at')->nullable();
    $table->integer('remaining_seconds')->nullable();
    $table->string('status', 20)->default('in_progress');
    $table->string('ip_address', 45)->nullable();
    $table->string('device_info', 255)->nullable();
    $table->timestamps();

    // Satu siswa hanya satu sesi per ujian
    $table->unique(['exam_id', 'student_id']);
});
```

### 3.4 Migration `invoices` + `payments`

```php
// create_invoices_table.php
Schema::create('invoices', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
    $table->string('invoice_number', 50)->unique();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignUuid('academic_year_id')->constrained('academic_years');
    $table->string('semester', 2)->default('1');
    $table->uuid('batch_id')->nullable();
    $table->string('status', 20)->default('unpaid');
        // unpaid, partial, paid, overdue, void
    $table->decimal('subtotal', 12, 2)->default(0);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('total', 12, 2)->default(0);
    $table->date('due_date')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('voided_at')->nullable();
    $table->text('void_reason')->nullable();
    $table->text('notes')->nullable();
    $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->index(['student_id', 'academic_year_id']);
    $table->index(['status', 'due_date']);
});

// create_invoice_items_table.php
Schema::create('invoice_items', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
    $table->foreignUuid('fee_type_id')->constrained('fee_types');
    $table->string('fee_name', 150);  // snapshot
    $table->text('description')->nullable();
    $table->smallInteger('quantity')->default(1);
    $table->decimal('unit_price', 12, 2);
    $table->decimal('subtotal', 12, 2);
    $table->smallInteger('period_month')->nullable();  // 1-12
    $table->smallInteger('period_year')->nullable();
    $table->timestamps();
});

// create_payments_table.php
Schema::create('payments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
    $table->string('payment_number', 50)->unique();
    $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    $table->string('paid_by', 100)->nullable();
    $table->foreignUuid('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
    $table->string('payment_channel', 30)->default('backend');
        // backend, portal, gateway
    $table->decimal('amount', 12, 2);
    $table->decimal('admin_fee', 12, 2)->default(0);
    $table->string('gateway_ref', 100)->nullable();
    $table->string('gateway_status', 30)->nullable();
    $table->string('proof_file', 255)->nullable();
    $table->string('status', 20)->default('pending');
        // pending, verified, rejected, void
    $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->text('reject_reason')->nullable();
    $table->date('payment_date');
    $table->timestamp('paid_at')->useCurrent();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['invoice_id']);
    $table->index(['student_id']);
    $table->index(['status', 'payment_date']);
});
```

### 3.5 Migration `grades` (Nilai per TP)

```php
Schema::create('grades', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
    $table->foreignUuid('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
    $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
    $table->string('jenis_nilai', 20);  // uh, sts, sas, p5, tugas
    $table->decimal('nilai', 5, 2);      // 0–100
    $table->text('deskripsi')->nullable();
    $table->string('sumber', 20)->default('manual'); // manual, ujian
    $table->foreignUuid('exam_result_id')->nullable()->constrained('exam_results')->nullOnDelete();
    $table->foreignUuid('created_by')->constrained('users');
    $table->timestamps();

    // Unique: satu TP dinilai satu kali per jenis per siswa
    $table->unique(['student_id', 'class_subject_id', 'learning_objective_id', 'jenis_nilai']);
});
```

---

## 4. Ringkasan Hubungan Relasi

| Model A | Relasi | Model B | Melalui |
|---|---|---|---|
| School | hasMany | AcademicYear, Major, SchoolClass, Subject, User, Student, Exam, FeeType, Invoice | – |
| AcademicYear | hasMany | Semester, SchoolClass, Invoice | – |
| Semester | hasMany | Grade, Report, Attendance, Exam, P5Project | – |
| SchoolClass | hasMany | ClassSubject, Student | – |
| Subject | hasMany | ClassSubject, LearningOutcome, QuestionBank, Exam | – |
| ClassSubject | belongsTo | SchoolClass, Subject, User(teacher) | – |
| Student | belongsToMany | Guardian(Parent) | parent_student |
| Student | hasMany | Grade, Report, Attendance, P5Assessment, ExamSession, ExamResult, Invoice, Payment | – |
| LearningOutcome (CP) | hasMany | LearningObjective (TP) | – |
| LearningObjective (TP) | hasMany | LearningObjectiveSubject, Grade, Question | – |
| Exam | hasMany | ExamQuestion, ExamSession, ExamResult | – |
| ExamSession | hasMany | ExamAnswer | – |
| ExamSession | hasOne | ExamResult | – |
| ExamResult | hasMany | Grade | exam_result_id FK |
| Invoice | hasMany | InvoiceItem, Payment | – |
| Payment | hasMany | PaymentLog | – |

---

## 5. Dukungan untuk Semua Flow

| Flow | Model Utama | API |
|---|---|---|
| **Siswa lihat nilai** | Student → Grade → LearningObjective → ClassSubject | `GET /api/grades` |
| **Siswa lihat rapor** | Student → Report → Semester | `GET /api/reports/{semesterId}` |
| **Siswa lihat presensi** | Student → Attendance → Semester | `GET /api/attendances` |
| **Guru input nilai** | User → ClassSubject → Grade → LearningObjective | `POST /api/teacher/grades` |
| **Wali kelas validasi rapor** | User → SchoolClass → Report (lock) | `POST /api/teacher/reports/lock` |
| **Exam CBT** | Exam → ExamSession → ExamAnswer → ExamResult | `POST /api/exams/{id}/start`, `submit` |
| **Exam → Nilai → Rapor** | ExamResult.tp_scores → Grade → Report | Event ExamGraded |
| **Bendahara generate tagihan** | FeeType → Invoice → InvoiceItem | `POST /api/teacher/invoices/generate` |
| **Ortu bayar** | Guardian → Invoice → Payment | `POST /api/payments/bills/{id}/pay` |
| **Bendahara verifikasi** | Payment → Invoice (update status) | `POST /api/teacher/payments/{id}/verify` |
| **Ortu lihat tagihan anak** | Guardian → Student → Invoice → Payment | `GET /api/payments/bills` |
| **Kepsek dashboard** | Report + Invoice + Payment aggregate | `GET /api/principal/dashboard` |

---

**Total**: **36 tabel**, **32 model Laravel**, mendukung seluruh flow yang telah dirancang dari prompt sebelumnya — portal siswa/ortu, exam CBT, pembayaran, dan backend untuk semua role (Admin, Guru, Wali Kelas, Bendahara, Kepala Sekolah).
