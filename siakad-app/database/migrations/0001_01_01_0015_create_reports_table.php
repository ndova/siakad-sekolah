<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0015_reports');
    }
};
