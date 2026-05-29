<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected string $backupPath;
    protected array $allowedRoles = ['superadmin', 'admin'];

    public function __construct()
    {
        $this->backupPath = storage_path('backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Halaman backup & restore.
     */
    public function index()
    {
        $backups = $this->listBackups();
        $dbDriver = config('database.default');
        $autoBackupEnabled = (bool) env('BACKUP_AUTO_ENABLED', false);
        $retentionDays = (int) env('BACKUP_RETENTION_DAYS', 30);
        return view('backend.system.backup', compact('backups', 'dbDriver', 'autoBackupEnabled', 'retentionDays'));
    }

    /**
     * Buat backup database.
     */
    public function create()
    {
        $timestamp = now()->format('Y_m_d_His');
        $dbDriver = config('database.default');
        $dbName = config("database.connections.{$dbDriver}.database");

        try {
            if (in_array($dbDriver, ['mysql', 'mariadb'])) {
                $filePath = "{$this->backupPath}/backup_{$timestamp}.sql";
                $this->backupMySql($filePath);
            } elseif ($dbDriver === 'pgsql') {
                $filePath = "{$this->backupPath}/backup_{$timestamp}.sql";
                $this->backupPostgres($filePath);
            } elseif ($dbDriver === 'sqlite') {
                $filePath = "{$this->backupPath}/backup_{$timestamp}.sqlite";
                File::copy(database_path('database.sqlite'), $filePath);
            } else {
                return back()->with('error', "Driver database {$dbDriver} tidak didukung untuk backup otomatis.");
            }

            return back()->with('success', 'Backup berhasil dibuat: ' . basename($filePath));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download file backup.
     */
    public function download(string $filename)
    {
        $filePath = "{$this->backupPath}/{$filename}";

        // Validasi — cegah path traversal
        if (!File::exists($filePath) || realpath($filePath) !== realpath($this->backupPath) . DIRECTORY_SEPARATOR . $filename) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return Response::download($filePath);
    }

    /**
     * Restore dari file backup.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
        ]);

        $filename = $request->input('backup_file');
        $filePath = "{$this->backupPath}/{$filename}";

        // Validasi keamanan
        if (!File::exists($filePath) || realpath($filePath) !== realpath($this->backupPath) . DIRECTORY_SEPARATOR . $filename) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        $dbDriver = config('database.default');

        try {
            if (in_array($dbDriver, ['mysql', 'mariadb'])) {
                $this->restoreMySql($filePath);
            } elseif ($dbDriver === 'pgsql') {
                $this->restorePostgres($filePath);
            } elseif ($dbDriver === 'sqlite') {
                $dbCurrent = database_path('database.sqlite');
                // Backup dulu sebelum restore
                copy($dbCurrent, "{$this->backupPath}/pre_restore_" . now()->format('Y_m_d_His') . '.sqlite');
                File::copy($filePath, $dbCurrent);
            } else {
                return back()->with('error', "Driver database {$dbDriver} tidak didukung untuk restore.");
            }

            return back()->with('success', 'Restore berhasil dari: ' . $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal restore: ' . $e->getMessage());
        }
    }

    /**
     * Hapus file backup.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $filePath = "{$this->backupPath}/{$filename}";

        if (!File::exists($filePath) || realpath($filePath) !== realpath($this->backupPath) . DIRECTORY_SEPARATOR . $filename) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        File::delete($filePath);
        return back()->with('success', 'File backup dihapus.');
    }

    /**
     * Restore dari file backup yang diupload.
     */
    public function restoreUpload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,sqlite,gz|max:51200', // max 50MB
        ]);

        $file = $request->file('backup_file');
        $filename = 'restore_upload_' . now()->format('Y_m_d_His') . '.' . $file->getClientOriginalExtension();

        // Simpan dulu ke storage
        $tempPath = "{$this->backupPath}/{$filename}";
        $file->move($this->backupPath, $filename);

        $dbDriver = config('database.default');

        try {
            if (in_array($dbDriver, ['mysql', 'mariadb'])) {
                $this->restoreMySql($tempPath);
            } elseif ($dbDriver === 'pgsql') {
                $this->restorePostgres($tempPath);
            } elseif ($dbDriver === 'sqlite') {
                $dbCurrent = database_path('database.sqlite');
                copy($dbCurrent, "{$this->backupPath}/pre_restore_" . now()->format('Y_m_d_His') . '.sqlite');
                File::copy($tempPath, $dbCurrent);
            } else {
                File::delete($tempPath);
                return back()->with('error', "Driver database {$dbDriver} tidak didukung.");
            }

            return back()->with('success', 'Restore berhasil dari file: ' . $file->getClientOriginalName());
        } catch (\Exception $e) {
            File::delete($tempPath);
            return back()->with('error', 'Gagal restore: ' . $e->getMessage());
        }
    }

    /**
     * Toggle auto-backup & update retensi via .env
     */
    public function settings(Request $request)
    {
        $request->validate([
            'backup_auto_enabled' => 'required|in:0,1',
            'backup_retention_days' => 'required|integer|min:1|max:365',
        ]);

        $enabled = $request->input('backup_auto_enabled');
        $retention = $request->input('backup_retention_days');

        $this->updateEnv([
            'BACKUP_AUTO_ENABLED' => $enabled,
            'BACKUP_RETENTION_DAYS' => $retention,
        ]);

        $status = $enabled ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Auto-backup {$status}. Retensi: {$retention} hari.");
    }

    // ─── HELPERS ──────────────────────────────────────────────────────

    protected function listBackups(): array
    {
        if (!File::exists($this->backupPath)) {
            return [];
        }

        $files = File::files($this->backupPath);
        $list = [];

        foreach ($files as $file) {
            $list[] = [
                'filename' => $file->getFilename(),
                'size'     => $this->formatSize($file->getSize()),
                'date'     => date('d M Y H:i:s', $file->getMTime()),
                'ext'      => $file->getExtension(),
            ];
        }

        // Urutkan terbaru di atas
        usort($list, fn($a, $b) => strcmp($b['filename'], $a['filename']));

        return $list;
    }

    protected function backupMySql(string $filePath): void
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Cari mysqldump binary
        $mysqldump = $this->findBinary(['mysqldump', 'mysqldump8', '/usr/bin/mysqldump', '/usr/local/bin/mysqldump']);

        if (!$mysqldump) {
            throw new \RuntimeException('mysqldump tidak ditemukan. Pastikan MySQL client tools terinstall.');
        }

        $command = [
            $mysqldump,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            '--complete-insert',
            $dbName,
        ];

        if ($password) {
            $command[] = '--password=' . $password;
        }

        $result = Process::run(implode(' ', array_map('escapeshellarg', $command)));
        $output = $commandOutput = null;

        // Gunakan exec untuk handle redirection
        $cmdStr = implode(' ', array_map('escapeshellarg', $command)) . ' > ' . escapeshellarg($filePath);
        exec($cmdStr, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("mysqldump gagal (exit code: {$exitCode})");
        }
    }

    protected function restoreMySql(string $filePath): void
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $mysql = $this->findBinary(['mysql', '/usr/bin/mysql', '/usr/local/bin/mysql']);

        if (!$mysql) {
            throw new \RuntimeException('mysql client tidak ditemukan.');
        }

        $command = [
            $mysql,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,
            $dbName,
        ];

        if ($password) {
            $command[] = '--password=' . $password;
        }

        $cmdStr = implode(' ', array_map('escapeshellarg', $command)) . ' < ' . escapeshellarg($filePath);
        exec($cmdStr, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("mysql restore gagal (exit code: {$exitCode})");
        }
    }

    protected function backupPostgres(string $filePath): void
    {
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port', 5432);
        $dbName = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        $pgDump = $this->findBinary(['pg_dump', '/usr/bin/pg_dump', '/usr/local/bin/pg_dump']);

        if (!$pgDump) {
            throw new \RuntimeException('pg_dump tidak ditemukan.');
        }

        // Set PGPASSWORD environment
        $envCmd = $password ? 'PGPASSWORD=' . escapeshellarg($password) . ' ' : '';

        $cmdStr = $envCmd . escapeshellarg($pgDump)
            . ' --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg($port)
            . ' --username=' . escapeshellarg($user)
            . ' --no-owner --no-acl --format=plain'
            . ' ' . escapeshellarg($dbName)
            . ' > ' . escapeshellarg($filePath);

        exec($cmdStr, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("pg_dump gagal (exit code: {$exitCode})");
        }
    }

    protected function restorePostgres(string $filePath): void
    {
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port', 5432);
        $dbName = config('database.connections.pgsql.database');
        $user = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        $psql = $this->findBinary(['psql', '/usr/bin/psql', '/usr/local/bin/psql']);

        if (!$psql) {
            throw new \RuntimeException('psql tidak ditemukan.');
        }

        $envCmd = $password ? 'PGPASSWORD=' . escapeshellarg($password) . ' ' : '';

        $cmdStr = $envCmd . escapeshellarg($psql)
            . ' --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg($port)
            . ' --username=' . escapeshellarg($user)
            . ' --dbname=' . escapeshellarg($dbName)
            . ' < ' . escapeshellarg($filePath);

        exec($cmdStr, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("psql restore gagal (exit code: {$exitCode})");
        }
    }

    protected function findBinary(array $candidates): ?string
    {
        foreach ($candidates as $cmd) {
            // Cek absolute path
            if (str_starts_with($cmd, '/') && file_exists($cmd) && is_executable($cmd)) {
                return $cmd;
            }

            // Cek via which/where
            if (PHP_OS_FAMILY === 'Windows') {
                $check = exec('where ' . escapeshellarg($cmd) . ' 2>NUL', $out, $code);
            } else {
                $check = exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null', $out, $code);
            }

            if ($code === 0 && !empty(trim(implode('', $out)))) {
                return trim(implode('', $out));
            }
        }

        return null;
    }

    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    /**
     * Update baris di file .env dengan aman.
     */
    protected function updateEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            throw new \RuntimeException('.env file tidak ditemukan.');
        }

        $content = File::get($envPath);

        foreach ($pairs as $key => $value) {
            $escaped = str_replace(['\\', '"'], ['\\\\', '\"'], (string) $value);

            if (preg_match("/^{$key}=.*$/m", $content)) {
                $content = preg_replace("/^{$key}=.*$/m", "{$key}=\"{$escaped}\"", $content);
            } else {
                $content .= PHP_EOL . "{$key}=\"{$escaped}\"";
            }
        }

        File::put($envPath, $content);

        // Hapus cache config agar env terbaca ulang
        \Illuminate\Support\Facades\Artisan::call('config:clear');
    }
}
