<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p5_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('dimensi_1', 20)->nullable();
            $table->string('dimensi_2', 20)->nullable();
            $table->string('dimensi_3', 20)->nullable();
            $table->string('dimensi_4', 20)->nullable();
            $table->string('dimensi_5', 20)->nullable();
            $table->string('dimensi_6', 20)->nullable();
            $table->text('catatan_proses')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0017_p5_assessments');
    }
};
