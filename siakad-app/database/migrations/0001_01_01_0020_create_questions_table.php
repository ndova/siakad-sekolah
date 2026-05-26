<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0020_questions');
    }
};
