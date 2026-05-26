<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. PERANGKAT FINGERPRINT ───
        Schema::create('fingerprint_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools');
            $table->string('name', 100);               // Nama perangkat (misal: "Mesin Gerbang Depan")
            $table->string('serial_number', 50)->unique(); // SN perangkat
            $table->string('ip_address', 45)->nullable();  // IP untuk koneksi
            $table->integer('port')->default(4370);        // Port (default ZKTeco: 4370)
            $table->string('model', 50)->nullable();       // Tipe: ZKTeco K40, F18, dsb
            $table->string('location', 200)->nullable();   // Lokasi fisik
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('status', 20)->default('offline'); // online/offline/error
            $table->json('config')->nullable();            // Konfigurasi tambahan
            $table->timestamps();
        });

        // ─── 2. LOG FINGERPRINT MENTAH ───
        Schema::create('finger_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('device_id')->constrained('fingerprint_devices');
            $table->string('pin', 30)->nullable();         // PIN / ID user di mesin
            $table->dateTime('scan_time');                 // Waktu tap
            $table->string('verify_mode', 10)->default('fp'); // fp= fingerprint, pw=password, card=rfid
            $table->string('io_mode', 10)->default('in');  // in/out
            $table->integer('work_code')->default(0);
            $table->boolean('is_processed')->default(false); // Sudah diproses jadi absensi?
            $table->uuid('attendance_id')->nullable();      // Link ke Attendance/StaffAttendance
            $table->string('attendance_type', 20)->nullable(); // student/staff
            $table->json('raw_data')->nullable();           // Data mentah dari mesin
            $table->timestamps();

            $table->index(['pin', 'scan_time']);
            $table->index(['is_processed']);
        });

        // ─── 3. PIN MAPPING (kode mesin → user SIAKAD) ───
        Schema::create('finger_pin_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools');
            $table->string('pin', 30);                     // PIN di mesin
            $table->string('entity_type', 20);             // student / staff
            $table->uuid('entity_id');                     // student_id / staff_id
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'pin', 'entity_type']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finger_pin_mappings');
        Schema::dropIfExists('finger_logs');
        Schema::dropIfExists('fingerprint_devices');
    }
};
