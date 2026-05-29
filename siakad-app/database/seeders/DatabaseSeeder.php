<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        $this->call([
            P1_CoreSeeder::class,
            P2_AcademicsSeeder::class,
            P3_FinanceSeeder::class,
            P4_DataSeeder::class,
            P5_PortalDataSeeder::class,
            P6_DapodikKurmerSeeder::class,
            ClassSubjectSeeder::class,
        ]);

        DB::statement('PRAGMA foreign_keys = ON');

        echo "\n✅ DATABASE SUKSES DISEED!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  Login (semua password: password123):\n";
        echo "  superadmin@siakad.test | admin@siakad.test\n";
        echo "  guru@siakad.test | walikelas@siakad.test\n";
        echo "  bendahara@siakad.test | kepsek@siakad.test\n";
        echo "  andi.pratama@siakad.test (siswa)\n";
        echo "  ortu.andi@siakad.test (orang tua)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}
