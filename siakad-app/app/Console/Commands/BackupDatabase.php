<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'backup:create
                            {--name= : Nama file backup opsional}
                            {--label= : Label tipe backup (manual/daily/weekly/monthly)}
                            {--max-age= : Hapus backup lebih tua dari x hari}';
    protected $description = 'Membuat backup database';

    public function handle(): int
    {
        $dbDriver = config('database.default');
        $backupPath = storage_path('backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $name = $this->option('name');
        $label = $this->option('label') ?? 'manual';
        if (!$name) {
            $name = 'backup_' . $label . '_' . now()->format('Y_m_d_His');
        }

        try {
            if (in_array($dbDriver, ['mysql', 'mariadb'])) {
                $this->backupMySql($backupPath, $name);
            } elseif ($dbDriver === 'pgsql') {
                $this->backupPostgres($backupPath, $name);
            } elseif ($dbDriver === 'sqlite') {
                $filePath = "{$backupPath}/{$name}.sqlite";
                File::copy(database_path('database.sqlite'), $filePath);
            } else {
                $this->error("Driver {$dbDriver} tidak didukung.");
                return self::FAILURE;
            }

            $this->info("✅ Backup berhasil: {$name}");

            // Cleanup backup lama berdasarkan max-age
            $maxAge = intval($this->option('max-age') ?? 0);
            if ($maxAge > 0) {
                $this->cleanOldBackups($backupPath, $maxAge);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function backupMySql(string $backupPath, string $name): void
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $mysqldump = $this->findBin(['mysqldump', 'mysqldump8']);
        if (!$mysqldump) {
            throw new \RuntimeException('mysqldump tidak ditemukan.');
        }

        $filePath = "{$backupPath}/{$name}.sql";
        $cmd = escapeshellarg($mysqldump)
            . ' --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg($port)
            . ' --user=' . escapeshellarg($user)
            . ($password ? ' --password=' . escapeshellarg($password) : '')
            . ' --single-transaction --routines --triggers --no-tablespaces --complete-insert'
            . ' ' . escapeshellarg($dbName)
            . ' > ' . escapeshellarg($filePath);

        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException("mysqldump gagal (exit: {$exitCode})");
        }
    }

    protected function backupPostgres(string $backupPath, string $name): void
    {
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port', 5432);
        $dbName = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        $pgDump = $this->findBin(['pg_dump']);
        if (!$pgDump) {
            throw new \RuntimeException('pg_dump tidak ditemukan.');
        }

        $filePath = "{$backupPath}/{$name}.sql";
        $envCmd = $password ? 'PGPASSWORD=' . escapeshellarg($password) . ' ' : '';

        $cmd = $envCmd . escapeshellarg($pgDump)
            . ' --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg($port)
            . ' --username=' . escapeshellarg($user)
            . ' --no-owner --no-acl --format=plain'
            . ' ' . escapeshellarg($dbName)
            . ' > ' . escapeshellarg($filePath);

        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException("pg_dump gagal (exit: {$exitCode})");
        }
    }

    protected function findBin(array $candidates): ?string
    {
        foreach ($candidates as $cmd) {
            $check = PHP_OS_FAMILY === 'Windows'
                ? exec('where ' . escapeshellarg($cmd) . ' 2>NUL', $out, $code)
                : exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null', $out, $code);
            if ($code === 0 && !empty(trim($check))) {
                return trim($check);
            }
        }
        return null;
    }

    /**
     * Hapus file backup yang lebih tua dari x hari.
     */
    protected function cleanOldBackups(string $backupPath, int $maxAgeDays): void
    {
        $cutoff = now()->subDays($maxAgeDays)->timestamp;
        $deleted = 0;

        foreach (File::files($backupPath) as $file) {
            // Hanya hapus backup otomatis (bukan manual)
            $filename = $file->getFilename();
            if (!str_contains($filename, 'backup_daily_') && !str_contains($filename, 'backup_weekly_') && !str_contains($filename, 'backup_monthly_')) {
                continue;
            }
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("🧹 {$deleted} backup lama dihapus (retensi {$maxAgeDays} hari).");
        }
    }
}
