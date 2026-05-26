<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── schools ───
        Schema::table('schools', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('npsn');
            $table->string('jenis_sekolah', 20)->default('SMK')->after('kode_dapodik');
            $table->string('status_sekolah', 20)->default('swasta')->after('jenis_sekolah'); // negeri/swasta
        });

        // ─── students ───
        Schema::table('students', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('nisn');
            $table->string('nik', 16)->nullable()->after('kode_dapodik');
            $table->boolean('is_verified_nisn')->default(false)->after('nik');
        });

        // ─── users (untuk GTK) ───
        Schema::table('users', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('nip');
            $table->string('nik', 16)->nullable()->after('kode_dapodik');
            $table->string('nuptk', 16)->nullable()->after('nik');
            $table->string('jenis_gtk', 30)->nullable()->after('role'); // guru_mapel/wali_kelas/kepsek/bk/tendik
            $table->string('status_kepegawaian', 20)->nullable()->after('jenis_gtk'); // PNS/PPPK/GTY/GTT/honorer
        });

        // ─── subjects ───
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('kode_dapodik', 30)->nullable()->after('code');
            $table->enum('kelompok', ['A','B','C','P5'])->default('A')->after('kategori');
            $table->string('fase', 5)->nullable()->after('kategori'); // E/F
            $table->boolean('is_pkl')->default(false)->after('fase');
            $table->boolean('is_p5')->default(false)->after('is_pkl');
            $table->integer('jam_semester')->nullable()->after('is_p5');
        });

        // ─── classes ───
        Schema::table('classes', function (Blueprint $table) {
            $table->string('kode_dapodik', 30)->nullable()->after('code');
            $table->foreignUuid('specialization_id')->nullable()->after('major_id')
                ->constrained('specializations')->nullOnDelete();
            $table->foreignUuid('kurikulum_id')->nullable()->after('specialization_id')
                ->constrained('curricula')->nullOnDelete();
            $table->integer('kapasitas')->default(36)->after('tingkat');
        });

        // ─── majors ───
        Schema::table('majors', function (Blueprint $table) {
            $table->string('kode_dapodik', 30)->nullable()->after('code');
            $table->string('bidang_keahlian', 200)->nullable()->after('name');
            $table->string('program_keahlian', 200)->nullable()->after('bidang_keahlian');
        });

        // ─── p5_projects ───
        Schema::table('p5_projects', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('tema');
            $table->integer('total_jam')->nullable()->after('tanggal_selesai');
        });

        // ─── p5_assessments ───
        Schema::table('p5_assessments', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('student_id');
        });

        // ─── attendances ───
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('student_id');
        });

        // ─── reports ───
        Schema::table('reports', function (Blueprint $table) {
            $table->string('kode_dapodik', 50)->nullable()->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('schools', fn($t) => $t->dropColumn(['kode_dapodik','jenis_sekolah','status_sekolah']));
        Schema::table('students', fn($t) => $t->dropColumn(['kode_dapodik','nik','is_verified_nisn']));
        Schema::table('users', fn($t) => $t->dropColumn(['kode_dapodik','nik','nuptk','jenis_gtk','status_kepegawaian']));
        Schema::table('subjects', fn($t) => $t->dropColumn(['kode_dapodik','kelompok','fase','is_pkl','is_p5','jam_semester']));
        Schema::table('classes', fn($t) => $t->dropForeign(['specialization_id','kurikulum_id']));
        Schema::table('classes', fn($t) => $t->dropColumn(['kode_dapodik','specialization_id','kurikulum_id','kapasitas']));
        Schema::table('majors', fn($t) => $t->dropColumn(['kode_dapodik','bidang_keahlian','program_keahlian']));
        Schema::table('p5_projects', fn($t) => $t->dropColumn(['kode_dapodik','total_jam']));
        Schema::table('p5_assessments', fn($t) => $t->dropColumn('kode_dapodik'));
        Schema::table('attendances', fn($t) => $t->dropColumn('kode_dapodik'));
        Schema::table('reports', fn($t) => $t->dropColumn('kode_dapodik'));
    }
};
