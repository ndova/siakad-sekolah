<?php

namespace Database\Seeders;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::where('is_active', true)->first();
        if (!$semester) {
            $this->command->error('Tidak ada semester aktif. Jalankan AcademicYearSeeder dulu.');
            return;
        }

        $classes = SchoolClass::where('is_active', true)->get();

        // ─── Konfigurasi Mapel per Jenis Kelas ─────────────────────
        // Format: ['subject_code' => jam_per_minggu]
        $ipaPlan = [
            'PAI'   => 2,  // Pendidikan Agama
            'PPKn'  => 2,  // PPKn
            'BIN'   => 4,  // Bahasa Indonesia
            'MAT'   => 4,  // Matematika
            'BIG'   => 3,  // Bahasa Inggris
            'PJOK'  => 2,  // PJOK
            'INF'   => 2,  // Informatika
            'SENI'  => 2,  // Seni Budaya
            'IPA'   => 5,  // IPA
            'FIS'   => 3,  // Fisika
            'SJH'   => 2,  // Sejarah
            'KWU'   => 2,  // Kewirausahaan
            'DDTKJ' => 4,  // Dasar-Dasar TKJ
            'DDRPL' => 4,  // Dasar-Dasar RPL
        ]; // Total: 41 → perlu 1 lagi

        $ipsPlan = [
            'PAI'   => 2,
            'PPKn'  => 2,
            'BIN'   => 4,
            'MAT'   => 4,
            'BIG'   => 3,
            'PJOK'  => 2,
            'INF'   => 2,
            'SENI'  => 2,
            'IPA'   => 3,
            'IPS'   => 5,  // IPS lebih banyak untuk jurusan IPS
            'SJH'   => 3,
            'KWU'   => 2,
            'DDTKJ' => 4,
            'DDRPL' => 4,
        ]; // Total: 42 ✓

        // Tambahkan IPS ke IPA plan agar genap 43
        $ipaPlan['IPS'] = 2; // Total: 43 ✓

        // Untuk kelas XI kejuruan, tambahkan PKL
        $xiPlan = array_merge($ipaPlan, [
            'PKL' => 3,  // Praktik Kerja Lapangan
            'FIS' => 4,  // Fisika lebih intensif di XI
            'KWU' => 3,  // Kewirausahaan lebih banyak
        ]);
        unset($xiPlan['IPS']); // XI IPA tidak perlu IPS
        // Total XI: ~44

        $subjects = Subject::all()->keyBy('code');

        $created = 0;

        foreach ($classes as $class) {
            // Tentukan plan berdasarkan kode kelas
            $plan = match (true) {
                str_contains($class->code, 'X-IPA')  => $ipaPlan,
                str_contains($class->code, 'X-IPS')  => $ipsPlan,
                str_contains($class->code, 'XI-IPA') => $xiPlan,
                default                              => $ipaPlan,
            };

            $totalJP = 0;
            foreach ($plan as $code => $jp) {
                $subject = $subjects->get($code);
                if (!$subject) {
                    $this->command->warn("  ⚠ Mapel kode '{$code}' tidak ditemukan, skip.");
                    continue;
                }

                ClassSubject::updateOrCreate(
                    [
                        'class_id'   => $class->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'id'             => (string) Str::uuid(),
                        'semester_id'    => $semester->id,
                        'teacher_id'     => null,
                        'kkm'            => 70,
                        'jam_per_minggu' => $jp,
                    ]
                );
                $totalJP += $jp;
                $created++;
            }

            $this->command->info("  ✓ {$class->code}: {$totalJP} JP/minggu");
        }

        $this->command->info("Total {$created} class-subject records created.");
    }
}
