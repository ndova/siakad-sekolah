<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Major;
use App\Models\ParentStudent;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class P1_CoreSeeder extends Seeder
{
    public static array $school, $ta, $semGanjil, $semGenap;
    public static array $users = [], $majors = [], $classes = [], $subjects = [];
    public static array $classSubjects = [], $students = [];

    public function run(): void
    {
        $school = School::create([
            'id' => '0199c000-0001-7000-8000-000000000001',
            'name' => 'SMA Negeri 1 Percontohan',
            'npsn' => '12345678',
            'address' => 'Jl. Pendidikan No. 1, Jakarta',
            'phone' => '021-1234567',
            'email' => 'info@sman1.sch.id',
            'website' => 'https://sman1.sch.id',
            'principal_name' => 'Dr. Kepala Sekolah, M.Pd.',
            'accreditation' => 'A',
            'established_year' => 1985,
            'is_active' => true,
            'portal_title' => 'Sistem Informasi Akademik',
            'welcome_text' => 'Selamat Datang! di Sistem Informasi Akademik',
            'tagline' => 'Akses nilai, presensi, ujian, dan pembayaran dalam satu portal terpadu.',
            'footer_text' => '© ' . date('Y') . ' SMA Negeri 1 Percontohan. All rights reserved.',
            'primary_color' => '#2563eb',
            'primary_color_light' => '#3b82f6',
            'tempat_cetak' => 'Jakarta',
            'vision' => 'Mewujudkan peserta didik yang beriman, bertakwa, berakhlak mulia, mandiri, bernalar kritis, dan berkebinekaan global.',
            'mission' => "1. Melaksanakan pembelajaran yang inovatif dan menyenangkan.\n2. Mengembangkan potensi peserta didik secara holistik.\n3. Menanamkan nilai-nilai karakter Profil Pelajar Pancasila.\n4. Menjalin kemitraan dengan orang tua dan masyarakat.",
        ]);

        $ta = AcademicYear::create(['id' => '0199c000-0002-7000-8000-000000000001', 'school_id' => $school->id, 'code' => '2025/2026', 'start_date' => '2025-07-14', 'end_date' => '2026-06-27', 'is_active' => true]);

        $semGanjil = Semester::create(['id' => '0199c000-0003-7000-8000-000000000001', 'academic_year_id' => $ta->id, 'semester_number' => 1, 'start_date' => '2025-07-14', 'end_date' => '2025-12-20', 'is_active' => true]);
        $semGenap = Semester::create(['id' => '0199c000-0003-7000-8000-000000000002', 'academic_year_id' => $ta->id, 'semester_number' => 2, 'start_date' => '2026-01-05', 'end_date' => '2026-06-27', 'is_active' => false]);

        self::$school = $school->toArray(); self::$ta = $ta->toArray();
        self::$semGanjil = $semGanjil->toArray(); self::$semGenap = $semGenap->toArray();

        // Users
        $uData = [
            ['id' => '0199c000-1000-7000-8000-000000000001', 'name' => 'Super Admin', 'email' => 'superadmin@siakad.test', 'role' => 'superadmin'],
            ['id' => '0199c000-1000-7000-8000-000000000002', 'name' => 'Admin Sekolah', 'email' => 'admin@siakad.test', 'role' => 'admin', 'nip' => '198501012010011001'],
            ['id' => '0199c000-1000-7000-8000-000000000003', 'name' => 'Budi Santoso, S.Pd.', 'email' => 'guru@siakad.test', 'role' => 'guru', 'nip' => '198802022011011002'],
            ['id' => '0199c000-1000-7000-8000-000000000004', 'name' => 'Dewi Lestari, S.Pd.', 'email' => 'walikelas@siakad.test', 'role' => 'walikelas', 'nip' => '198903032012011003'],
            ['id' => '0199c000-1000-7000-8000-000000000005', 'name' => 'Bendahara Sekolah', 'email' => 'bendahara@siakad.test', 'role' => 'bendahara', 'nip' => '199004042013011004'],
            ['id' => '0199c000-1000-7000-8000-000000000006', 'name' => 'Dr. Kepala Sekolah', 'email' => 'kepsek@siakad.test', 'role' => 'kepsek', 'nip' => '197505052000121001'],
            ['id' => '0199c000-1000-7000-8000-000000000007', 'name' => 'Guru B.Inggris', 'email' => 'guru2@siakad.test', 'role' => 'guru', 'nip' => '199105052015011005'],
            ['id' => '0199c000-1000-7000-8000-000000000008', 'name' => 'Guru Matematika', 'email' => 'guru3@siakad.test', 'role' => 'guru', 'nip' => '199206062016011006'],
        ];

        foreach ($uData as $u) {
            self::$users[$u['email']] = User::create(['id' => $u['id'], 'school_id' => $school->id, 'name' => $u['name'], 'email' => $u['email'], 'password' => bcrypt('password123'), 'role' => $u['role'], 'nip' => $u['nip'] ?? null, 'is_active' => true])->toArray();
        }

        self::$majors['ipa'] = Major::create(['id' => '0199c000-2000-7000-8000-000000000001', 'school_id' => $school->id, 'code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam', 'is_active' => true])->toArray();
        self::$majors['ips'] = Major::create(['id' => '0199c000-2000-7000-8000-000000000002', 'school_id' => $school->id, 'code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial', 'is_active' => true])->toArray();

        $wl = User::find(self::$users['walikelas@siakad.test']['id']);
        $mk = fn(string $id, string $code, string $tkt, string $mjr, $wali = null) => SchoolClass::create(['id' => $id, 'school_id' => $school->id, 'academic_year_id' => $ta->id, 'major_id' => self::$majors[$mjr]['id'], 'code' => $code, 'tingkat' => $tkt, 'jenjang' => 'SMA', 'wali_kelas_id' => $wali?->id, 'is_active' => true])->toArray();

        self::$classes['X-IPA-1'] = $mk('0199c000-3000-7000-8000-000000000001', 'X-IPA-1', 'X', 'ipa', $wl);
        self::$classes['X-IPS-1'] = $mk('0199c000-3000-7000-8000-000000000003', 'X-IPS-1', 'X', 'ips');
        self::$classes['XI-IPA-1'] = $mk('0199c000-3000-7000-8000-000000000004', 'XI-IPA-1', 'XI', 'ipa');

        $subs = [
            ['id' => '0199c000-4000-7000-8000-000000000001', 'code' => 'PAI', 'name' => 'Pendidikan Agama Islam'],
            ['id' => '0199c000-4000-7000-8000-000000000003', 'code' => 'BIN', 'name' => 'Bahasa Indonesia'],
            ['id' => '0199c000-4000-7000-8000-000000000004', 'code' => 'MTK', 'name' => 'Matematika'],
            ['id' => '0199c000-4000-7000-8000-000000000005', 'code' => 'ING', 'name' => 'Bahasa Inggris'],
            ['id' => '0199c000-4000-7000-8000-000000000006', 'code' => 'FIS', 'name' => 'Fisika'],
            ['id' => '0199c000-4000-7000-8000-000000000009', 'code' => 'SJH', 'name' => 'Sejarah'],
        ];

        foreach ($subs as $s) {
            self::$subjects[$s['code']] = Subject::create(['id' => $s['id'], 'school_id' => $school->id, 'code' => $s['code'], 'name' => $s['name'], 'kategori' => in_array($s['code'], ['FIS']) ? 'kejuruan' : 'umum', 'is_active' => true])->toArray();
        }

        $t1 = User::find(self::$users['guru@siakad.test']['id']);
        $t2 = User::find(self::$users['guru2@siakad.test']['id']);
        $t3 = User::find(self::$users['guru3@siakad.test']['id']);

        $clsId = self::$classes['X-IPA-1']['id'];
        $ipaSubjects = ['PAI', 'BIN', 'MTK', 'ING', 'FIS', 'SJH'];
        foreach ($ipaSubjects as $c) {
            $t = match ($c) { 'ING' => $t2, 'MTK' => $t3, default => $t1 };
            self::$classSubjects["X-IPA-1_{$c}"] = ClassSubject::create(['id' => Str::uuid(), 'class_id' => $clsId, 'subject_id' => self::$subjects[$c]['id'], 'teacher_id' => $t->id, 'kkm' => 70, 'jam_per_minggu' => 2])->toArray();
        }

        $siswaData = [
            ['nama' => 'Andi Pratama', 'nis' => '2025001', 'id' => '0199c000-6000-7000-8000-000000000001', 'kls' => 'X-IPA-1', 'email' => 'andi.pratama@siakad.test'],
            ['nama' => 'Bunga Lestari', 'nis' => '2025002', 'id' => '0199c000-6000-7000-8000-000000000002', 'kls' => 'X-IPA-1', 'email' => 'bunga.lestari@siakad.test'],
            ['nama' => 'Cahya Ramadhan', 'nis' => '2025003', 'id' => '0199c000-6000-7000-8000-000000000003', 'kls' => 'X-IPA-1', 'email' => 'cahya.ramadhan@siakad.test'],
            ['nama' => 'Dian Permata', 'nis' => '2025004', 'id' => '0199c000-6000-7000-8000-000000000004', 'kls' => 'X-IPA-1', 'email' => 'dian.permata@siakad.test'],
            ['nama' => 'Eko Prasetyo', 'nis' => '2025005', 'id' => '0199c000-6000-7000-8000-000000000005', 'kls' => 'X-IPS-1', 'email' => 'eko.prasetyo@siakad.test'],
            ['nama' => 'Fitri Handayani', 'nis' => '2025006', 'id' => '0199c000-6000-7000-8000-000000000006', 'kls' => 'X-IPS-1', 'email' => 'fitri.handayani@siakad.test'],
        ];

        foreach ($siswaData as $i => $s) {
            $u = User::create(['id' => Str::uuid(), 'school_id' => $school->id, 'name' => $s['nama'], 'email' => $s['email'], 'password' => bcrypt('password123'), 'role' => 'siswa', 'is_active' => true]);
            self::$users[$s['email']] = $u->toArray();
            self::$students[$s['nama']] = Student::create(['id' => $s['id'], 'user_id' => $u->id, 'school_id' => $school->id, 'class_id' => self::$classes[$s['kls']]['id'], 'nis' => $s['nis'], 'nisn' => '00' . (12345678 + $i), 'nama_lengkap' => $s['nama'], 'jk' => $i % 2 == 0 ? 'L' : 'P', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2009-01-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT), 'agama' => 'Islam', 'alamat' => "Jl. Merdeka No. " . ($i + 1), 'phone' => '0812' . rand(10000000, 99999999), 'status' => 'aktif'])->toArray();
        }

        // Guardians
        $gData = [
            ['nama' => 'Bapak Budi', 'email' => 'ortu.andi@siakad.test', 'jk' => 'L', 'hub' => 'ayah', 'anak' => 'Andi Pratama'],
            ['nama' => 'Ibu Sari', 'email' => 'ortu.bunga@siakad.test', 'jk' => 'P', 'hub' => 'ibu', 'anak' => 'Bunga Lestari'],
        ];

        foreach ($gData as $g) {
            $u = User::create(['id' => Str::uuid(), 'school_id' => $school->id, 'name' => $g['nama'], 'email' => $g['email'], 'password' => bcrypt('password123'), 'role' => 'orang_tua', 'is_active' => true]);
            self::$users[$g['email']] = $u->toArray();
            $guardian = Guardian::create(['id' => Str::uuid(), 'user_id' => $u->id, 'nama_lengkap' => $g['nama'], 'jk' => $g['jk'], 'hubungan' => $g['hub'], 'pekerjaan' => 'Wiraswasta', 'phone' => '081200000000', 'alamat' => 'Jl. Merdeka, Jakarta']);
            ParentStudent::create(['id' => Str::uuid(), 'parent_id' => $guardian->id, 'student_id' => self::$students[$g['anak']]['id'], 'is_primary' => true]);
        }

        echo "✅ Core data seeded\n";
    }
}
