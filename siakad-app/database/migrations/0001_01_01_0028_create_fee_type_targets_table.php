<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_type_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fee_type_id')->constrained('fee_types')->cascadeOnDelete();
            $table->string('target_level', 10)->default('all');
            $table->string('jenjang', 5)->nullable();
            $table->smallInteger('tingkat')->nullable();
            $table->foreignUuid('jurusan_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->decimal('nominal_override', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['fee_type_id', 'target_level', 'jenjang', 'tingkat', 'jurusan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0028_fee_type_targets');
    }
};
