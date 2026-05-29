<?php

use App\Jobs\ExportDapodikJob;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

// ─── AUTOBACKUP SCHEDULE ───────────────────────────────────────
// Dikendalikan oleh .env: BACKUP_AUTO_ENABLED=true, BACKUP_RETENTION_DAYS=30

if (env('BACKUP_AUTO_ENABLED', false)) {
    $retention = env('BACKUP_RETENTION_DAYS', 30);

    // Harian: setiap hari jam 02:00
    Schedule::command('backup:create --label=daily --max-age=' . $retention)
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/backup-schedule.log'));

    // Mingguan: setiap Senin jam 03:00
    Schedule::command('backup:create --label=weekly --max-age=' . ($retention * 4))
        ->weeklyOn(1, '03:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/backup-schedule.log'));

    // Bulanan: setiap tanggal 1 jam 04:00
    Schedule::command('backup:create --label=monthly --max-age=' . ($retention * 12))
        ->monthlyOn(1, '04:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/backup-schedule.log'));
}
