<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0021_exams');
    }
};
