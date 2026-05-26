<?php
// Script untuk generate semua file migration SIAKAD
// Jalankan: php database/generate_migrations.php

$migrations = [];
$num = 2;

function add(&$migrations, &$num, $name, $code) {
    $prefix = str_pad($num, 4, '0', STR_PAD_LEFT);
    $filename = "0001_01_01_{$prefix}_create_{$name}_table.php";
    $migrations[] = ['file' => $filename, 'code' => $code];
    $num++;
}

$header = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n";

$footer = "    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('{table}');\n    }\n};\n";

// 2. academic_years
add($migrations, $num, 'academic_years', <<<'SQL'
        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 9);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'code']);
        });
SQL);

// 3. semesters
add($migrations, $num, 'semesters', <<<'SQL'
        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->smallInteger('semester_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
SQL);

// 4. majors
add($migrations, $num, 'majors', <<<'SQL'
        Schema::create('majors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'code']);
        });
SQL);

// 5. classes
add($migrations, $num, 'classes', <<<'SQL'
        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUuid('major_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->string('code', 20);
            $table->smallInteger('tingkat');
            $table->string('jenjang', 5);
            $table->foreignUuid('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
SQL);

// 6. subjects
add($migrations, $num, 'subjects', <<<'SQL'
        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 150);
            $table->string('kategori', 20)->default('umum');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'code']);
        });
SQL);

// 7. class_subject
add($migrations, $num, 'class_subject', <<<'SQL'
        Schema::create('class_subject', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('kkm', 5, 2)->default(75);
            $table->smallInteger('jam_per_minggu')->default(2);
            $table->timestamps();
            $table->unique(['class_id', 'subject_id']);
        });
SQL);

// 8. students
add($migrations, $num, 'students', <<<'SQL'
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('nisn', 10)->unique();
            $table->string('nis', 20);
            $table->string('nama_lengkap', 200);
            $table->char('jk', 1);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ayah', 200)->nullable();
            $table->string('nama_ibu', 200)->nullable();
            $table->string('status', 20)->default('aktif');
            $table->date('tanggal_masuk')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
SQL);

// 9. parents
add($migrations, $num, 'parents', <<<'SQL'
        Schema::create('parents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nama_lengkap', 200);
            $table->char('jk', 1);
            $table->string('hubungan', 20);
            $table->string('pekerjaan', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
SQL);

// 10. parent_student
add($migrations, $num, 'parent_student', <<<'SQL'
        Schema::create('parent_student', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unique(['parent_id', 'student_id']);
        });
SQL);

// 11. curricula
add($migrations, $num, 'curricula', <<<'SQL'
        Schema::create('curricula', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 150);
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
SQL);

// 12. learning_outcomes (CP)
add($migrations, $num, 'learning_outcomes', <<<'SQL'
        Schema::create('learning_outcomes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('phase', 5);
            $table->string('code', 30);
            $table->text('description');
            $table->smallInteger('urutan')->default(1);
            $table->timestamps();
        });
SQL);

// 13. learning_objectives (TP)
add($migrations, $num, 'learning_objectives', <<<'SQL'
        Schema::create('learning_objectives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('learning_outcome_id')->constrained('learning_outcomes')->cascadeOnDelete();
            $table->string('code', 30);
            $table->text('description');
            $table->string('level_kognitif', 5)->nullable();
            $table->smallInteger('urutan')->default(1);
            $table->timestamps();
        });
SQL);

// 14. learning_objective_subjects (ATP)
add($migrations, $num, 'learning_objective_subjects', <<<'SQL'
        Schema::create('learning_objective_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->smallInteger('urutan_ajar')->default(1);
            $table->timestamps();
        });
SQL);

// 15. grades - SKIP dulu karena FK ke exam_results yang belum dibuat
// Akan dibuat setelah exam tables

// 16. reports
add($migrations, $num, 'reports', <<<'SQL'
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 5)->nullable();
            $table->text('deskripsi_cp')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->foreignUuid('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'semester_id', 'class_subject_id']);
        });
SQL);

// 17. p5_projects
add($migrations, $num, 'p5_projects', <<<'SQL'
        Schema::create('p5_projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('tema', 100);
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->jsonb('class_ids')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
SQL);

// 18. p5_assessments
add($migrations, $num, 'p5_assessments', <<<'SQL'
        Schema::create('p5_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('dimensi_1', 20)->nullable();
            $table->string('dimensi_2', 20)->nullable();
            $table->string('dimensi_3', 20)->nullable();
            $table->string('dimensi_4', 20)->nullable();
            $table->string('dimensi_5', 20)->nullable();
            $table->string('dimensi_6', 20)->nullable();
            $table->text('catatan_proses')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
SQL);

// 19. attendances
add($migrations, $num, 'attendances', <<<'SQL'
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->nullable()->constrained('class_subject')->nullOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('status', 10);
            $table->string('keterangan', 255)->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['student_id', 'class_subject_id', 'tanggal']);
        });
SQL);

// 20. question_banks
add($migrations, $num, 'question_banks', <<<'SQL'
        Schema::create('question_banks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 200);
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users');
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });
SQL);

// 21. questions
add($migrations, $num, 'questions', <<<'SQL'
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->foreignUuid('learning_objective_id')->nullable()->constrained('learning_objectives')->nullOnDelete();
            $table->string('type', 20);
            $table->text('content');
            $table->jsonb('media')->nullable();
            $table->jsonb('options')->nullable();
            $table->text('answer_key')->nullable();
            $table->decimal('score', 5, 2)->default(10);
            $table->string('level_kognitif', 5)->nullable();
            $table->string('difficulty', 10)->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
SQL);

// 22. exams
add($migrations, $num, 'exams', <<<'SQL'
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('title', 200);
            $table->string('type', 20);
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->jsonb('class_ids')->nullable();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->integer('duration');
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
SQL);

// 23. exam_questions
add($migrations, $num, 'exam_questions', <<<'SQL'
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->smallInteger('urutan');
            $table->decimal('score_override', 5, 2)->nullable();
            $table->unique(['exam_id', 'question_id']);
        });
SQL);

// 24. exam_sessions
add($migrations, $num, 'exam_sessions', <<<'SQL'
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
            $table->unique(['exam_id', 'student_id']);
        });
SQL);

// 25. exam_answers
add($migrations, $num, 'exam_answers', <<<'SQL'
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignUuid('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->jsonb('selected_options')->nullable();
            $table->text('text_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['exam_session_id', 'exam_question_id']);
        });
SQL);

// 26. exam_results
add($migrations, $num, 'exam_results', <<<'SQL'
        Schema::create('exam_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_session_id')->unique()->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->decimal('total_score', 6, 2)->default(0);
            $table->integer('correct_count')->default(0);
            $table->integer('wrong_count')->default(0);
            $table->jsonb('tp_scores')->nullable();
            $table->boolean('is_passed')->default(false);
            $table->foreignUuid('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
        });
SQL);

// 27. grades (FK ke exam_results, harus setelah exam_results)
add($migrations, $num, 'grades', <<<'SQL'
        Schema::create('grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('jenis_nilai', 20);
            $table->decimal('nilai', 5, 2);
            $table->text('deskripsi')->nullable();
            $table->string('sumber', 20)->default('manual');
            $table->foreignUuid('exam_result_id')->nullable()->constrained('exam_results')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['student_id', 'class_subject_id', 'learning_objective_id', 'jenis_nilai']);
        });
SQL);

// 28. fee_types
add($migrations, $num, 'fee_types', <<<'SQL'
        Schema::create('fee_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('category', 20)->default('rutin');
            $table->decimal('nominal', 12, 2)->default(0);
            $table->string('billing_period', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'code']);
        });
SQL);

// 29. fee_type_targets
add($migrations, $num, 'fee_type_targets', <<<'SQL'
        Schema::create('fee_type_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fee_type_id')->constrained('fee_types')->cascadeOnDelete();
            $table->string('target_level', 10)->default('all');
            $table->string('jenjang', 5)->nullable();
            $table->smallInteger('tingkat')->nullable();
            $table->foreignUuid('jurusan_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->decimal('nominal_override', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['fee_type_id', 'target_level', 'jenjang', 'tingkat', 'jurusan_id']);
        });
SQL);

// 30. invoices
add($migrations, $num, 'invoices', <<<'SQL'
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('semester', 2)->default('1');
            $table->uuid('batch_id')->nullable();
            $table->string('status', 20)->default('unpaid');
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
SQL);

// 31. invoice_items
add($migrations, $num, 'invoice_items', <<<'SQL'
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('fee_type_id')->constrained('fee_types')->cascadeOnDelete();
            $table->string('fee_name', 150);
            $table->text('description')->nullable();
            $table->smallInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->smallInteger('period_month')->nullable();
            $table->smallInteger('period_year')->nullable();
            $table->timestamps();
        });
SQL);

// 32. payment_methods
add($migrations, $num, 'payment_methods', <<<'SQL'
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('type', 20)->default('offline');
            $table->string('account_number', 50)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'code']);
        });
SQL);

// 33. payments
add($migrations, $num, 'payments', <<<'SQL'
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('payment_number', 50)->unique();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('paid_by', 100)->nullable();
            $table->foreignUuid('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_channel', 30)->default('backend');
            $table->decimal('amount', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->string('gateway_ref', 100)->nullable();
            $table->string('gateway_status', 30)->nullable();
            $table->string('proof_file', 255)->nullable();
            $table->string('status', 20)->default('pending');
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
SQL);

// 34. payment_logs
add($migrations, $num, 'payment_logs', <<<'SQL'
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
SQL);

// 35. student_class_histories
add($migrations, $num, 'student_class_histories', <<<'SQL'
        Schema::create('student_class_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->date('mulai');
            $table->date('selesai')->nullable();
            $table->timestamps();
        });
SQL);

// 36. notifications
add($migrations, $num, 'notifications', <<<'SQL'
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('title', 200);
            $table->text('message');
            $table->string('reference_type', 30)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });
SQL);

// Generate files
$dir = __DIR__ . '/migrations';
$count = 0;
foreach ($migrations as $m) {
    $code = $header . $m['code'] . "\n" . str_replace('{table}', str_replace('create_', '', str_replace('_table', '', pathinfo($m['file'], PATHINFO_FILENAME))), $footer);
    file_put_contents($dir . '/' . $m['file'], $code);
    $count++;
    echo "Created: {$m['file']}\n";
}

echo "\nTotal: {$count} migration files generated.\n";
