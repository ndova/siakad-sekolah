<?php

namespace App\Services\Dapodik;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Generate CSV/JSON export files for Dapodik.
 */
class DapodikExporter
{
    protected string $disk = 'local';
    protected string $folder = 'exports/dapodik';

    /**
     * Export rows to CSV file.
     *
     * @param string $label      File label (e.g., 'siswa_rombel')
     * @param array  $rows       Array of associative arrays
     * @param array  $columns    Optional column order (if null, use first row keys)
     * @return string            Relative file path
     */
    public function toCsv(string $label, array $rows, ?array $columns = null): string
    {
        if (empty($rows)) {
            throw new \InvalidArgumentException('No data to export');
        }

        $filename = sprintf('%s/%s_%s.csv', $this->folder, $label, now()->format('Y-m-d_His'));
        $columns = $columns ?? array_keys($rows[0]);

        $content = $this->buildCsvContent($rows, $columns);
        Storage::disk($this->disk)->put($filename, $content);

        return $filename;
    }

    /**
     * Export rows to JSON file.
     */
    public function toJson(string $label, array $rows): string
    {
        if (empty($rows)) {
            throw new \InvalidArgumentException('No data to export');
        }

        $filename = sprintf('%s/%s_%s.json', $this->folder, $label, now()->format('Y-m-d_His'));
        Storage::disk($this->disk)->put($filename, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $filename;
    }

    /**
     * Build CSV content with proper escaping.
     */
    protected function buildCsvContent(array $rows, array $columns): string
    {
        $output = fopen('php://temp', 'r+');

        // Header
        fputcsv($output, $columns);

        // Data rows
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($output, $line);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        // Tambahkan BOM untuk UTF-8 compatibility (Excel)
        return "\xEF\xBB\xBF" . $content;
    }

    /**
     * Get download URL for a file.
     */
    public function downloadUrl(string $path): string
    {
        return route('dapodik.download', ['path' => base64_encode($path)]);
    }

    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Delete old export files.
     */
    public function cleanup(int $olderThanDays = 30): int
    {
        $files = Storage::disk($this->disk)->files($this->folder);
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = Storage::disk($this->disk)->lastModified($file);
            if ($lastModified && (time() - $lastModified) > ($olderThanDays * 86400)) {
                Storage::disk($this->disk)->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}
