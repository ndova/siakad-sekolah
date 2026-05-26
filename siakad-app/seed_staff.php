<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = DB::connection();

// Get school
$school = $db->table('schools')->first();
if (!$school) {
    echo "No school found!\n";
    exit(1);
}

// Get internal users
$users = $db->table('users')
    ->whereIn('role', ['guru', 'walikelas', 'bendahara', 'kepsek', 'admin'])
    ->where('is_active', true)
    ->get()
    ->keyBy('email');

echo "Found " . count($users) . " internal users\n";

$staffMappings = [
    ['email' => 'guru@siakad.test', 'jabatan' => 'guru', 'nama' => 'Budi Santoso, S.Pd.', 'nip' => '198802022011011002', 'nuptk' => '987654322011011002', 'jk' => 'L', 'gol' => 'III/c', 'pend' => 'S1 Pendidikan Bahasa Indonesia', 'ttl' => 'Surabaya', 'tgl' => '1988-02-02', 'agama' => 'Islam', 'tgl_msk' => '2011-01-01'],
    ['email' => 'walikelas@siakad.test', 'jabatan' => 'walikelas', 'nama' => 'Dewi Lestari, S.Pd.', 'nip' => '198903032012011003', 'nuptk' => '987654332012011003', 'jk' => 'P', 'gol' => 'III/b', 'pend' => 'S1 Pendidikan Biologi', 'ttl' => 'Bandung', 'tgl' => '1989-03-03', 'agama' => 'Islam', 'tgl_msk' => '2012-01-01'],
    ['email' => 'bendahara@siakad.test', 'jabatan' => 'bendahara', 'nama' => 'Bendahara Sekolah', 'nip' => '199004042013011004', 'nuptk' => null, 'jk' => 'P', 'gol' => 'III/a', 'pend' => 'S1 Akuntansi', 'ttl' => 'Jakarta', 'tgl' => '1990-04-04', 'agama' => 'Islam', 'tgl_msk' => '2013-01-01'],
    ['email' => 'kepsek@siakad.test', 'jabatan' => 'kepsek', 'nama' => 'Dr. Kepala Sekolah', 'nip' => '197505052000121001', 'nuptk' => null, 'jk' => 'L', 'gol' => 'IV/a', 'pend' => 'S3 Manajemen Pendidikan', 'ttl' => 'Yogyakarta', 'tgl' => '1975-05-05', 'agama' => 'Islam', 'tgl_msk' => '2000-12-01'],
    ['email' => 'guru2@siakad.test', 'jabatan' => 'guru', 'nama' => 'Guru B.Inggris', 'nip' => '199105052015011005', 'nuptk' => '987654362015011005', 'jk' => 'L', 'gol' => 'III/b', 'pend' => 'S1 Pendidikan Bahasa Inggris', 'ttl' => 'Medan', 'tgl' => '1991-05-05', 'agama' => 'Kristen', 'tgl_msk' => '2015-01-01'],
    ['email' => 'guru3@siakad.test', 'jabatan' => 'guru', 'nama' => 'Guru Matematika', 'nip' => '199206062016011006', 'nuptk' => '987654372016011006', 'jk' => 'P', 'gol' => 'III/b', 'pend' => 'S1 Pendidikan Matematika', 'ttl' => 'Semarang', 'tgl' => '1992-06-06', 'agama' => 'Katolik', 'tgl_msk' => '2016-01-01'],
];

$adminUser = $users->get('admin@siakad.test');
$seededStaff = [];

foreach ($staffMappings as $s) {
    if (!isset($users[$s['email']])) {
        echo "  SKIP: {$s['email']} not found\n";
        continue;
    }
    $user = $users[$s['email']];

    $staffId = \Illuminate\Support\Str::uuid();
    $db->table('staff')->insert([
        'id' => $staffId,
        'school_id' => $school->id,
        'user_id' => $user->id,
        'nama_lengkap' => $s['nama'],
        'nip' => $s['nip'],
        'nuptk' => $s['nuptk'],
        'jabatan' => $s['jabatan'],
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
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $seededStaff[$s['email']] = (object)['id' => $staffId, 'jabatan' => $s['jabatan']];
    echo "  + {$s['nama']} ({$s['jabatan']})\n";
}

echo "Staff seeded: " . count($seededStaff) . "\n";

// Seed attendance for the last 20 working days
$staffStatusPool = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'terlambat', 'izin', 'sakit'];
$count = 0;

foreach ($seededStaff as $email => $staff) {
    for ($d = 0; $d < 20; $d++) {
        $tanggal = now()->subDays($d + 1);
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

        $db->table('staff_attendances')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'staff_id' => $staff->id,
            'school_id' => $school->id,
            'tanggal' => $tanggal->format('Y-m-d'),
            'status' => $status,
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'keterangan' => $keterangan,
            'source' => ['manual', 'self_service', 'mesin_absen'][array_rand([0, 1, 2])],
            'device_sn' => 'FP001',
            'created_by' => $adminUser->id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $count++;
    }
}

echo "Attendance records: $count\n";
echo "DONE!\n";
