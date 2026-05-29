<?php

use App\Jobs\ExportDapodikJob;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dapodik:sync-all', function () {
    $school = School::first();
    if (!$school || !$school->npsn) {
        $this->error('NPSN belum diisi. Isi NPSN di Pengaturan terlebih dahulu.');
        return 1;
    }

    $classIds = SchoolClass::where('school_id', $school->id)
        ->where('is_active', true)
        ->pluck('id');

    $semesterId = Semester::whereHas('academicYear',
        fn($q) => $q->where('school_id', $school->id)
    )->where('is_active', true)->value('id');

    if ($classIds->isEmpty()) {
        $this->error('Tidak ada kelas aktif.');
        return 1;
    }
    if (!$semesterId) {
        $this->error('Tidak ada semester aktif.');
        return 1;
    }

    ExportDapodikJob::dispatch('student', $classIds, $semesterId, $school->id);
    ExportDapodikJob::dispatch('teacher', $classIds, $semesterId, $school->id);
    ExportDapodikJob::dispatch('grade',   $classIds, $semesterId, $school->id);

    $this->info('3 job ekspor Dapodik berhasil dikirim.');
    $this->info('Cek hasilnya di Log Sinkronisasi: /backend/dapodik/logs');
})->purpose('Ekspor semua data ke format Dapodik');
