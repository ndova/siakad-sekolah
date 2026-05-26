<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. KONSENTRASI KEAHLIAN ───
        Schema::create('specializations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools');
            $table->foreignUuid('major_id')->constrained('majors');
            $table->string('kode_dapodik', 30)->nullable();
            $table->string('code', 20);
            $table->string('name', 200);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['major_id', 'code']);
        });

        // ─── 2. PENUGASAN MENGAJAR GURU ───
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('class_subject_id')->constrained('class_subject');
            $table->foreignUuid('semester_id')->constrained('semesters');
            $table->integer('jam_per_minggu')->default(0);
            $table->string('kode_dapodik', 50)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'class_subject_id', 'semester_id'], 'uq_ta_usr_cs_smt');
        });

        // ─── 3. JADWAL PELAJARAN ───
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_subject_id')->constrained('class_subject');
            $table->foreignUuid('semester_id')->constrained('semesters');
            $table->tinyInteger('hari');       // 1=Senin .. 6=Sabtu
            $table->tinyInteger('jam_ke');     // 1..10
            $table->string('ruangan', 50)->nullable();
            $table->string('kode_dapodik', 50)->nullable();
            $table->timestamps();
            $table->unique(['class_subject_id', 'hari', 'jam_ke'], 'uq_sched_cs_day_hour');
        });

        // ─── 4. ALOKASI BEBAN JAM ───
        Schema::create('lesson_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_id')->constrained('classes');
            $table->foreignUuid('subject_id')->constrained('subjects');
            $table->foreignUuid('semester_id')->constrained('semesters');
            $table->integer('jam_per_minggu')->default(0);
            $table->integer('total_jam_semester')->nullable();
            $table->string('kode_dapodik', 50)->nullable();
            $table->timestamps();
            $table->unique(['class_id', 'subject_id', 'semester_id'], 'uq_lh_class_subj_smt');
        });

        // ─── 5. MAPPING KODE DAPODIK ───
        Schema::create('dapodik_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type', 50);
            $table->uuid('local_id');
            $table->string('dapodik_id', 100)->nullable();
            $table->string('dapodik_code', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'local_id'], 'uq_dm_entity_local');
            $table->index(['entity_type', 'dapodik_id'], 'idx_dm_entity_dapodik');
        });

        // ─── 6. LOG SINKRONISASI ───
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('direction', 10);       // import/export
            $table->string('entity_type', 50);
            $table->string('status', 20)->default('pending');
            $table->integer('total_records')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('error_details')->nullable();
            $table->uuid('triggered_by')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // ─── 7. PKL (PRAKTIK KERJA LAPANGAN) ───
        Schema::create('pkl_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students');
            $table->foreignUuid('class_id')->constrained('classes');
            $table->foreignUuid('semester_id')->constrained('semesters');
            $table->string('nama_dudi', 200);
            $table->string('alamat_dudi')->nullable();
            $table->string('pembimbing_dudi', 100)->nullable();
            $table->string('pembimbing_sekolah', 100)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('total_jam')->default(0);
            $table->string('kode_dapodik', 50)->nullable();
            $table->timestamps();
        });

        // ─── 8. PENILAIAN PKL ───
        Schema::create('pkl_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pkl_record_id')->constrained('pkl_records');
            $table->string('aspek', 100);
            $table->decimal('nilai', 5, 2);
            $table->string('predikat', 5)->nullable();  // A/B/C/D
            $table->text('catatan')->nullable();
            $table->string('kode_dapodik', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_assessments');
        Schema::dropIfExists('pkl_records');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('dapodik_mappings');
        Schema::dropIfExists('lesson_hours');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('specializations');
    }
};
