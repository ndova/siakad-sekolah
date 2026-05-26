<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Tanggal cetak rapor (kustom)
            $table->boolean('rapor_tgl_otomatis')->default(true)->after('tempat_cetak');
            $table->tinyInteger('rapor_tanggal')->nullable()->after('rapor_tgl_otomatis');
            $table->tinyInteger('rapor_bulan')->nullable()->after('rapor_tanggal');
            $table->year('rapor_tahun')->nullable()->after('rapor_bulan');

            // Urutan modul (1-6, semakin kecil tampil duluan)
            $table->tinyInteger('rapor_order_identitas')->default(1)->after('rapor_tahun');
            $table->tinyInteger('rapor_order_nilai')->default(2)->after('rapor_order_identitas');
            $table->tinyInteger('rapor_order_p5')->default(3)->after('rapor_order_nilai');
            $table->tinyInteger('rapor_order_presensi')->default(4)->after('rapor_order_p5');
            $table->tinyInteger('rapor_order_catatan')->default(5)->after('rapor_order_presensi');
            $table->tinyInteger('rapor_order_ttd')->default(6)->after('rapor_order_catatan');

            // Tampilkan / sembunyikan modul
            $table->boolean('rapor_show_nilai')->default(true)->after('rapor_order_ttd');
            $table->boolean('rapor_show_p5')->default(true)->after('rapor_show_nilai');
            $table->boolean('rapor_show_presensi')->default(true)->after('rapor_show_p5');
            $table->boolean('rapor_show_catatan')->default(true)->after('rapor_show_presensi');
            $table->boolean('rapor_show_ttd_ortu')->default(true)->after('rapor_show_catatan');
            $table->boolean('rapor_show_ttd_walikelas')->default(true)->after('rapor_show_ttd_ortu');
            $table->boolean('rapor_show_ttd_kepsek')->default(true)->after('rapor_show_ttd_walikelas');

            // Label kustom
            $table->string('rapor_label_nilai', 50)->nullable()->after('rapor_show_ttd_kepsek');
            $table->string('rapor_label_p5', 50)->nullable()->after('rapor_label_nilai');
            $table->string('rapor_label_presensi', 50)->nullable()->after('rapor_label_p5');
            $table->string('rapor_label_catatan', 50)->nullable()->after('rapor_label_presensi');
            $table->string('rapor_label_ttd', 60)->nullable()->after('rapor_label_catatan');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'rapor_tgl_otomatis', 'rapor_tanggal', 'rapor_bulan', 'rapor_tahun',
                'rapor_order_identitas', 'rapor_order_nilai', 'rapor_order_p5',
                'rapor_order_presensi', 'rapor_order_catatan', 'rapor_order_ttd',
                'rapor_show_nilai', 'rapor_show_p5', 'rapor_show_presensi',
                'rapor_show_catatan', 'rapor_show_ttd_ortu', 'rapor_show_ttd_walikelas',
                'rapor_show_ttd_kepsek',
                'rapor_label_nilai', 'rapor_label_p5', 'rapor_label_presensi',
                'rapor_label_catatan', 'rapor_label_ttd',
            ]);
        });
    }
};
