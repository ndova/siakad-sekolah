<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore {filename : Nama file backup di storage/backups}';
    protected $description = 'Restore database dari file backup';

    public function handle(): int
    {
        $filename = $this->argument('filename');
        $backupPath = storage_path('backups');
        $filePath = "{$backupPath}/{$filename}";

        if (!File::exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return self::FAILURE;
        }

        if (!$this->confirm("⚠️ Restore akan menimpa SEMUA data saat ini. Lanjutkan?")) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        $dbDriver = config('database.default');

        try {
            if (in_array($dbDriver, ['mysql', 'mariadb'])) {
                $this->restoreMySql($filePath);
            } elseif ($dbDriver === 'pgsql') {
                $this->restorePostgres($filePath);
            } elseif ($dbDriver === 'sqlite') {
                // Backup dulu sebelum restore
                $preRestore = "{$backupPath}/pre_restore_" . now()->format('Y_m_d_His') . '.sqlite';
                copy(database_path('database.sqlite'), $preRestore);
                $this->info("Backup pra-restore disimpan: " . basename($preRestore));
                File::copy($filePath, database_path('database.sqlite'));
            } else {
                $this->error("Driver {$dbDriver} tidak didukung.");
                return self::FAILURE;
            }

            $this->info("✅ Restore berhasil dari: {$filename}");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function restoreMySql(string $filePath): void
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $mysql = $this->findBin(['mysql']);
        if (!$mysql) {
            throw new \RuntimeException('mysql client tidak ditemukan.');
        }

        $cmd = escapeshellarg($mysql)
            . ' --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg($port)
            . ' --user=' . escapeshellarg($user)
            . ($password ? ' --password=' . escapeshellarg($password) : '')
            . ' ' . escapeshellarg($dbName)
            . ' < ' . escapeshellarg($filePath);

        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException("mysql restore gagal (exit: {$exitCode})");
        }
    }

    protected function restorePostgres(string $filePath): void
    {
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port', 5432);
        $dbName = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        $psql = $this->findBin(['psql']);
        if (!$psql) {
            throw new \RuntimeException('psql tidak ditemukan.');
        }

        $envCmd = $password ? 'PGPASSWORD=' . escapeshellarg($password) . ' ' : '';

        $cmd = $envCmd . escapeshellarg($psql)
            . ' --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg($port)
            . ' --username=' . escapeshellarg($user)
            . ' --dbname=' . escapeshellarg($dbName)
            . ' < ' . escapeshellarg($filePath);

        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException("psql restore gagal (exit: {$exitCode})");
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
}
