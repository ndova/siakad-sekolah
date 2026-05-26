<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0024_exam_answers');
    }
};
