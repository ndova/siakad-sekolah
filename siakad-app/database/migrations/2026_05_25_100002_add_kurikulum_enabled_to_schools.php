<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('kurikulum_kurmer_enabled')->default(true)->after('rapor_label_ttd');
            $table->boolean('kurikulum_k13_enabled')->default(false)->after('kurikulum_kurmer_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['kurikulum_kurmer_enabled', 'kurikulum_k13_enabled']);
        });
    }
};
