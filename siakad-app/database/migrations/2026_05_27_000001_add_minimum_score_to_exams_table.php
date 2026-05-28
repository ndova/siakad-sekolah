<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->decimal('minimum_score', 5, 2)->nullable()->after('total_score')
                ->comment('KKM / nilai minimal kelulusan ujian. NULL = gunakan 70% dari total_score');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('minimum_score');
        });
    }
};
