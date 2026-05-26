<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_objectives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('learning_outcome_id')->constrained('learning_outcomes')->cascadeOnDelete();
            $table->string('code', 30);
            $table->text('description');
            $table->string('level_kognitif', 5)->nullable();
            $table->smallInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0013_learning_objectives');
    }
};
