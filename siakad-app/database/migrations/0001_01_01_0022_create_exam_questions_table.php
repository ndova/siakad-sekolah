<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->smallInteger('urutan');
            $table->decimal('score_override', 5, 2)->nullable();
            $table->unique(['exam_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0022_exam_questions');
    }
};
