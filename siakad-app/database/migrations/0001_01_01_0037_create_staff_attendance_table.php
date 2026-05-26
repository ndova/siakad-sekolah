<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel StaffAttendance — absensi pegawai harian.
     * Relasi: Staff (1) → (N) StaffAttendance
     *         School (1) → (N) StaffAttendance
     * 
     * Status: hadir, izin, sakit, alfa, terlambat
     * Sumber: manual, self_service, mesin_absen
     */
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('check_in_time')->nullable()->comment('Jam masuk (nullable = tidak tap-in)');
            $table->time('check_out_time')->nullable()->comment('Jam pulang (nullable = tidak tap-out)');
            $table->string('status', 15)->default('hadir')->comment('hadir, izin, sakit, alfa, terlambat');
            $table->string('keterangan', 255)->nullable();
            $table->string('source', 20)->default('manual')->comment('manual, self_service, mesin_absen');
            $table->string('device_sn', 50)->nullable()->comment('Serial number mesin absen (jika source=mesin_absen)');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('User yang menginput');
            $table->timestamps();

            // Satu staff hanya satu record absen per hari
            $table->unique(['staff_id', 'tanggal'], 'staff_attendance_unique_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
