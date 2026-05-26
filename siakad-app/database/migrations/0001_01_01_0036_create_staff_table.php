<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel Staff — profil pegawai (guru, kepsek, bendahara, BK, TU, dll.)
     * Relasi 1:1 dengan users (satu user = satu staff)
     * Relasi 1:N dengan schools (satu school punya banyak staff)
     */
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap', 200);
            $table->string('nip', 30)->nullable()->comment('Nomor Induk Pegawai');
            $table->string('nuptk', 30)->nullable()->comment('NUPTK untuk guru');
            $table->string('jabatan', 50)->comment('guru, kepsek, bendahara, bk, walikelas, tu, staff, pustakawan, laboran, satpam, kebersihan, dll');
            $table->string('golongan', 10)->nullable()->comment('III/a, III/b, IV/a, dsb');
            $table->string('pendidikan_terakhir', 100)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->char('jk', 1)->nullable()->comment('L / P');
            $table->string('agama', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('photo', 255)->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
