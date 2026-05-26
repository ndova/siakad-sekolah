<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_subject_id')->nullable()->constrained('class_subject')->nullOnDelete();
            $table->foreignUuid('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('status', 10);
            $table->string('keterangan', 255)->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['student_id', 'class_subject_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0018_attendances');
    }
};
