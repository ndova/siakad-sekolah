<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Major;
use App\Models\Specialization;
use App\Models\Subject;

/**
 * Seed data Kurikulum Merdeka SMK.
 * Diperlukan untuk testing integrasi Dapodik.
 */
class P6_DapodikKurmerSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            echo "⚠️  Tidak ada data sekolah. Jalankan P1_CoreSeeder dulu.\n";
            return;
        }

        $this->seedMajorsAndSpecializations($school);
        $this->seedKurmerSubjects($school);

        echo "✅ Seeder Dapodik & Kurikulum Merdeka SMK selesai.\n";
    }

    protected function seedMajorsAndSpecializations(School $school): void
    {
        // Program Keahlian SMK
        $majors = [
            [
                'code' => 'TKJ',
                'name' => 'Teknik Komputer dan Jaringan',
                'bidang_keahlian' => 'Teknologi Informasi dan Komunikasi',
                'program_keahlian' => 'Teknik Komputer dan Informatika',
                'kode_dapodik' => '071',
            ],
            [
                'code' => 'RPL',
                'name' => 'Rekayasa Perangkat Lunak',
                'bidang_keahlian' => 'Teknologi Informasi dan Komunikasi',
                'program_keahlian' => 'Teknik Komputer dan Informatika',
                'kode_dapodik' => '072',
            ],
            [
                'code' => 'AKL',
                'name' => 'Akuntansi dan Keuangan Lembaga',
                'bidang_keahlian' => 'Bisnis dan Manajemen',
                'program_keahlian' => 'Akuntansi dan Keuangan',
                'kode_dapodik' => '041',
            ],
        ];

        foreach ($majors as $m) {
            Major::updateOrCreate(
                ['school_id' => $school->id, 'code' => $m['code']],
                array_merge($m, ['school_id' => $school->id, 'is_active' => true])
            );
        }

        $tkj = Major::where('school_id', $school->id)->where('code', 'TKJ')->first();
        $rpl = Major::where('school_id', $school->id)->where('code', 'RPL')->first();

        // Konsentrasi Keahlian
        if ($tkj) {
            Specialization::updateOrCreate(
                ['major_id' => $tkj->id, 'code' => 'TKJ-ADM'],
                ['school_id' => $school->id, 'name' => 'Administrasi Jaringan', 'kode_dapodik' => 'TKJ01', 'is_active' => true]
            );
            Specialization::updateOrCreate(
                ['major_id' => $tkj->id, 'code' => 'TKJ-CYB'],
                ['school_id' => $school->id, 'name' => 'Keamanan Jaringan', 'kode_dapodik' => 'TKJ02', 'is_active' => true]
            );
        }

        if ($rpl) {
            Specialization::updateOrCreate(
                ['major_id' => $rpl->id, 'code' => 'RPL-WEB'],
                ['school_id' => $school->id, 'name' => 'Web Development', 'kode_dapodik' => 'RPL01', 'is_active' => true]
            );
        }
    }

    protected function seedKurmerSubjects(School $school): void
    {
        $subjects = [
            // ─── Kelompok A: Umum ───
            ['code'=>'PAI',  'name'=>'Pendidikan Agama Islam dan Budi Pekerti',   'kelompok'=>'A','kode_dapodik'=>'A01','jam_semester'=>72],
            ['code'=>'PPKn', 'name'=>'Pendidikan Pancasila dan Kewarganegaraan',  'kelompok'=>'A','kode_dapodik'=>'A02','jam_semester'=>54],
            ['code'=>'BIN',  'name'=>'Bahasa Indonesia',                          'kelompok'=>'A','kode_dapodik'=>'A03','jam_semester'=>72],
            ['code'=>'MAT',  'name'=>'Matematika',                                'kelompok'=>'A','kode_dapodik'=>'A04','jam_semester'=>90],
            ['code'=>'BIG',  'name'=>'Bahasa Inggris',                            'kelompok'=>'A','kode_dapodik'=>'A05','jam_semester'=>72],
            ['code'=>'PJOK', 'name'=>'Pendidikan Jasmani, Olahraga dan Kesehatan','kelompok'=>'A','kode_dapodik'=>'A06','jam_semester'=>54],
            ['code'=>'INF',  'name'=>'Informatika',                               'kelompok'=>'A','kode_dapodik'=>'A07','jam_semester'=>72],
            ['code'=>'SENI', 'name'=>'Seni Budaya',                               'kelompok'=>'A','kode_dapodik'=>'A08','jam_semester'=>36],
            ['code'=>'IPA',  'name'=>'Ilmu Pengetahuan Alam',                     'kelompok'=>'A','kode_dapodik'=>'A09','jam_semester'=>54],
            ['code'=>'IPS',  'name'=>'Ilmu Pengetahuan Sosial',                   'kelompok'=>'A','kode_dapodik'=>'A10','jam_semester'=>54],

            // ─── Kelompok B: Kejuruan ───
            ['code'=>'DDTKJ','name'=>'Dasar-Dasar Teknik Jaringan',   'kelompok'=>'B','kode_dapodik'=>'B01','jam_semester'=>144],
            ['code'=>'DDRPL','name'=>'Dasar-Dasar Pemrograman',       'kelompok'=>'B','kode_dapodik'=>'B02','jam_semester'=>144],
            ['code'=>'KWU',  'name'=>'Kewirausahaan',                 'kelompok'=>'B','kode_dapodik'=>'B03','jam_semester'=>72],

            // ─── Kelompok C: PKL ───
            ['code'=>'PKL',  'name'=>'Praktik Kerja Lapangan',        'kelompok'=>'C','kode_dapodik'=>'C01','jam_semester'=>630,'is_pkl'=>true],

            // ─── P5 ───
            ['code'=>'P5',   'name'=>'Projek Penguatan Profil Pelajar Pancasila','kelompok'=>'P5','kode_dapodik'=>'P501','jam_semester'=>288,'is_p5'=>true],
        ];

        foreach ($subjects as $s) {
            Subject::updateOrCreate(
                ['school_id' => $school->id, 'code' => $s['code']],
                array_merge($s, [
                    'school_id' => $school->id,
                    'kategori' => $s['kelompok'] === 'P5' ? 'p5' : ($s['kelompok'] === 'C' ? 'kejuruan' : 'umum'),
                    'is_active' => true,
                ])
            );
        }
    }
}
