<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\Report;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class P4_DataSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = P1_CoreSeeder::$school['id'];
        $semId = P1_CoreSeeder::$semGanjil['id'];
        $teacherId = P1_CoreSeeder::$users['guru@siakad.test']['id'];

        // Grades untuk 4 siswa X-IPA-1
        $nilai = [
            'Andi Pratama' => [85, 90, 78, 88, 82, 85],
            'Bunga Lestari' => [92, 88, 85, 90, 87, 91],
            'Cahya Ramadhan' => [75, 80, 72, 78, 76, 74],
            'Dian Permata' => [88, 92, 85, 90, 86, 89],
        ];

        foreach ($nilai as $name => $vals) {
            foreach (['BIN' => 0, 'MTK' => 3] as $code => $offset) {
                $csKey = "X-IPA-1_{$code}";
                if (!isset(P1_CoreSeeder::$classSubjects[$csKey])) continue;
                $csId = P1_CoreSeeder::$classSubjects[$csKey]['id'];

                for ($j = 0; $j < 3; $j++) {
                    $tpCode = "TP-{$code}-" . ($j + 1);
                    if (!isset(P2_AcademicsSeeder::$learningObjectives[$tpCode])) continue;

                    Grade::create([
                        'id' => Str::uuid(), 'student_id' => P1_CoreSeeder::$students[$name]['id'],
                        'class_subject_id' => $csId,
                        'learning_objective_id' => P2_AcademicsSeeder::$learningObjectives[$tpCode]['id'],
                        'semester_id' => $semId,
                        'jenis_nilai' => $j === 0 ? 'formatif' : 'sumatif',
                        'nilai' => $vals[$j + $offset],
                        'created_by' => $teacherId,
                    ]);
                }
            }
        }

        // Attendance
        $csKey = 'X-IPA-1_BIN';
        if (isset(P1_CoreSeeder::$classSubjects[$csKey])) {
            $csId = P1_CoreSeeder::$classSubjects[$csKey]['id'];
            $statusPool = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'terlambat', 'izin', 'sakit'];

            foreach (['Andi Pratama', 'Bunga Lestari', 'Cahya Ramadhan', 'Dian Permata'] as $name) {
                for ($d = 0; $d < 10; $d++) {
                    Attendance::create([
                        'id' => Str::uuid(), 'student_id' => P1_CoreSeeder::$students[$name]['id'],
                        'class_subject_id' => $csId, 'semester_id' => $semId,
                        'tanggal' => now()->subDays($d + 1)->format('Y-m-d'),
                        'status' => $statusPool[array_rand($statusPool)],
                        'created_by' => $teacherId,
                    ]);
                }
            }
        }

        // P5 Project
        $p5 = P5Project::create([
            'id' => Str::uuid(), 'school_id' => $schoolId, 'semester_id' => $semId,
            'tema' => 'Kearifan Lokal', 'judul' => 'Melestarikan Budaya Daerah',
            'deskripsi' => 'Proyek P5 tema kearifan lokal.',
            'class_ids' => [P1_CoreSeeder::$classes['X-IPA-1']['id']],
            'tanggal_mulai' => now()->subMonths(2), 'tanggal_selesai' => now()->addMonths(1),
            'created_by' => $teacherId,
        ]);

        foreach (['Andi Pratama', 'Bunga Lestari', 'Cahya Ramadhan', 'Dian Permata'] as $name) {
            P5Assessment::create([
                'id' => Str::uuid(), 'p5_project_id' => $p5->id,
                'student_id' => P1_CoreSeeder::$students[$name]['id'],
                'dimensi_1' => 'BSH', 'dimensi_2' => 'SB', 'dimensi_3' => 'BSH',
                'dimensi_4' => 'MB', 'dimensi_5' => 'SB', 'dimensi_6' => 'BSH',
                'catatan_proses' => 'Siswa menunjukkan perkembangan baik.',
                'created_by' => $teacherId,
            ]);
        }

        // Reports (lock rapor)
        $wlId = P1_CoreSeeder::$users['walikelas@siakad.test']['id'];
        foreach (['Andi Pratama', 'Bunga Lestari', 'Cahya Ramadhan', 'Dian Permata'] as $name) {
            foreach (['BIN', 'MTK', 'ING'] as $code) {
                $csKey = "X-IPA-1_{$code}";
                if (!isset(P1_CoreSeeder::$classSubjects[$csKey])) continue;
                $csId = P1_CoreSeeder::$classSubjects[$csKey]['id'];

                $avg = Grade::where('student_id', P1_CoreSeeder::$students[$name]['id'])
                    ->where('class_subject_id', $csId)
                    ->where('semester_id', $semId)
                    ->avg('nilai');
                $avg = $avg !== null ? round((float) $avg, 2) : 0;
                $predikat = $avg >= 90 ? 'A' : ($avg >= 80 ? 'B' : ($avg >= 70 ? 'C' : 'D'));

                Report::create([
                    'id' => Str::uuid(), 'student_id' => P1_CoreSeeder::$students[$name]['id'],
                    'semester_id' => $semId, 'class_subject_id' => $csId,
                    'nilai_akhir' => $avg, 'predikat' => $predikat,
                    'is_locked' => true, 'locked_by' => $wlId, 'locked_at' => now(),
                ]);
            }
        }

        // ─── Staff profiles from internal users ───────────────
        $staffMappings = [
            'guru@siakad.test' => 'guru',
            'walikelas@siakad.test' => 'walikelas',
            'bendahara@siakad.test' => 'bendahara',
            'kepsek@siakad.test' => 'kepsek',
            'guru2@siakad.test' => 'guru',
            'guru3@siakad.test' => 'guru',
        ];

        $seededStaff = [];
        $staffEmails = [
            ['nama' => 'Budi Santoso, S.Pd.', 'email' => 'guru@siakad.test', 'nip' => '198802022011011002', 'nuptk' => '987654322011011002', 'jk' => 'L', 'gol' => 'III/c', 'pend' => 'S1 Pendidikan Bahasa Indonesia', 'ttl' => 'Surabaya', 'tgl' => '1988-02-02', 'agama' => 'Islam', 'tgl_msk' => '2011-01-01'],
            ['nama' => 'Dewi Lestari, S.Pd.', 'email' => 'walikelas@siakad.test', 'nip' => '198903032012011003', 'nuptk' => '987654332012011003', 'jk' => 'P', 'gol' => 'III/b', 'pend' => 'S1 Pendidikan Biologi', 'ttl' => 'Bandung', 'tgl' => '1989-03-03', 'agama' => 'Islam', 'tgl_msk' => '2012-01-01'],
            ['nama' => 'Bendahara Sekolah', 'email' => 'bendahara@siakad.test', 'nip' => '199004042013011004', 'nuptk' => null, 'jk' => 'P', 'gol' => 'III/a', 'pend' => 'S1 Akuntansi', 'ttl' => 'Jakarta', 'tgl' => '1990-04-04', 'agama' => 'Islam', 'tgl_msk' => '2013-01-01'],
            ['nama' => 'Dr. Kepala Sekolah', 'email' => 'kepsek@siakad.test', 'nip' => '197505052000121001', 'nuptk' => null, 'jk' => 'L', 'gol' => 'IV/a', 'pend' => 'S3 Manajemen Pendidikan', 'ttl' => 'Yogyakarta', 'tgl' => '1975-05-05', 'agama' => 'Islam', 'tgl_msk' => '2000-12-01'],
            ['nama' => 'Guru B.Inggris', 'email' => 'guru2@siakad.test', 'nip' => '199105052015011005', 'nuptk' => '987654362015011005', 'jk' => 'L', 'gol' => 'III/b', 'pend' => 'S1 Pendidikan Bahasa Inggris', 'ttl' => 'Medan', 'tgl' => '1991-05-05', 'agama' => 'Kristen', 'tgl_msk' => '2015-01-01'],
            ['nama' => 'Guru Matematika', 'email' => 'guru3@siakad.test', 'nip' => '199206062016011006', 'nuptk' => '987654372016011006', 'jk' => 'P', 'gol' => 'III/b', 'pend' => 'S1 Pendidikan Matematika', 'ttl' => 'Semarang', 'tgl' => '1992-06-06', 'agama' => 'Katolik', 'tgl_msk' => '2016-01-01'],
        ];

        foreach ($staffEmails as $s) {
            if (!isset(P1_CoreSeeder::$users[$s['email']])) continue;
            $user = P1_CoreSeeder::$users[$s['email']];

            $staff = Staff::create([
                'id' => Str::uuid(),
                'school_id' => $schoolId,
                'user_id' => $user['id'],
                'nama_lengkap' => $s['nama'],
                'nip' => $s['nip'],
                'nuptk' => $s['nuptk'],
                'jabatan' => $staffMappings[$s['email']] ?? 'guru',
                'golongan' => $s['gol'],
                'pendidikan_terakhir' => $s['pend'],
                'tempat_lahir' => $s['ttl'],
                'tanggal_lahir' => $s['tgl'],
                'jk' => $s['jk'],
                'agama' => $s['agama'],
                'alamat' => 'Jl. Pendidikan No. ' . rand(1, 100) . ', Jakarta',
                'phone' => '0812' . rand(10000000, 99999999),
                'tanggal_masuk' => $s['tgl_msk'],
                'is_active' => true,
            ]);

            $seededStaff[$s['email']] = $staff;
        }

        echo "✅ Staff profiles seeded (" . count($seededStaff) . ")\n";

        // ─── Staff Attendance Seed ──────────────────────────
        $staffStatusPool = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'terlambat', 'izin', 'sakit'];

        foreach ($seededStaff as $email => $staff) {
            for ($d = 0; $d < 20; $d++) {
                $tanggal = now()->subDays($d + 1);
                // Skip weekends
                if ($tanggal->isWeekend()) continue;

                $status = $staffStatusPool[array_rand($staffStatusPool)];
                $checkIn = null;
                $checkOut = null;

                if ($status === 'terlambat') {
                    $checkIn = sprintf('%02d:%02d:00', rand(7, 8), rand(0, 59));
                } elseif ($status === 'hadir') {
                    $checkIn = sprintf('06:%02d:00', rand(30, 59));
                }

                if (in_array($status, ['hadir', 'terlambat'])) {
                    $checkOut = sprintf('%02d:%02d:00', rand(14, 17), rand(0, 59));
                }

                $keterangan = null;
                if ($status === 'izin') {
                    $keterangan = ['Acara keluarga', 'Keperluan pribadi', 'Ada urusan di luar kota'][array_rand([0, 1, 2])];
                } elseif ($status === 'sakit') {
                    $keterangan = ['Demam', 'Flu', 'Sakit kepala'][array_rand([0, 1, 2])];
                }

                StaffAttendance::create([
                    'id' => Str::uuid(),
                    'staff_id' => $staff->id,
                    'school_id' => $schoolId,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'status' => $status,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'keterangan' => $keterangan,
                    'source' => ['manual', 'self_service', 'mesin_absen'][array_rand([0, 1, 2])],
                    'device_sn' => 'FP001',
                    'created_by' => P1_CoreSeeder::$users['admin@siakad.test']['id'],
                ]);
            }
        }

        echo "✅ Data seeded (grades, attendance, P5, reports, staff, staff_attendance)\n";
    }
}
