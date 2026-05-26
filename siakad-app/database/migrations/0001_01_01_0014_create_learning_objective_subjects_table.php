<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_objective_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->constrained('class_subject')->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->smallInteger('urutan_ajar')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0014_learning_objective_subjects');
    }
};
