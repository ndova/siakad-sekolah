<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0026_grades');
    }
};
