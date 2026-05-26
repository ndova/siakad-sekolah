<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0012_learning_outcomes');
    }
};
