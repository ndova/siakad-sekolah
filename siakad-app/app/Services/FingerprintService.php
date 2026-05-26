<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use App\Models\FingerLog;
use App\Models\FingerPinMapping;
use App\Models\Attendance;
use App\Models\StaffAttendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk sinkronisasi data fingerprint dari mesin absen.
 * 
 * Mendukung:
 * - Pull log dari mesin (via API / file upload)
 * - Process log → generate Attendance & StaffAttendance
 * - Mapping PIN mesin → siswa/pegawai SIAKAD
 */
class FingerprintService
{
    // ─── PULL / UPLOAD LOG ──────────────────────────────────

    /**
     * Simpan log mentah dari mesin fingerprint (array of arrays).
     * Format per baris: [pin, scan_time, verify_mode, io_mode, work_code]
     */
    public function storeRawLogs(string $deviceId, array $logs): int
    {
        $count = 0;
        $device = FingerprintDevice::findOrFail($deviceId);
        $schoolId = $device->school_id;

        foreach ($logs as $log) {
            // Cek duplikat: pin + scan_time + device
            $exists = FingerLog::where('device_id', $deviceId)
                ->where('pin', $log['pin'])
                ->where('scan_time', $log['scan_time'])
                ->exists();

            if ($exists) continue;

            FingerLog::create([
                'device_id' => $deviceId,
                'pin' => $log['pin'],
                'scan_time' => $log['scan_time'],
                'verify_mode' => $log['verify_mode'] ?? 'fp',
                'io_mode' => $log['io_mode'] ?? 'in',
                'work_code' => $log['work_code'] ?? 0,
                'raw_data' => $log['raw_data'] ?? null,
            ]);

            $count++;
        }

        // Update last_sync_at
        $device->update(['last_sync_at' => now(), 'status' => 'online']);

        return $count;
    }

    // ─── PROCESS LOG → ATTENDANCE ───────────────────────────

    /**
     * Proses finger_logs yang belum diproses untuk tanggal tertentu.
     * Hasil: insert ke Attendance (siswa) atau StaffAttendance (pegawai).
     */
    public function processLogs(string $schoolId, string $date, string $defaultStatus = 'hadir'): array
    {
        $logs = FingerLog::with('device')
            ->unprocessed()
            ->byDate($date)
            ->get();

        $processed = 0;
        $skipped = 0;
        $errors = [];

        // Group by pin
        $grouped = $logs->groupBy('pin');

        foreach ($grouped as $pin => $pinLogs) {
            $mapping = FingerPinMapping::resolvePin($schoolId, $pin);

            if (!$mapping || !$mapping['entity']) {
                $skipped += $pinLogs->count();
                $errors[] = "PIN {$pin}: tidak ditemukan mapping ke siswa/pegawai";
                continue;
            }

            $sorted = $pinLogs->sortBy('scan_time');
            $firstScan = $sorted->first();
            $lastScan = $sorted->last();

            if ($mapping['type'] === 'student') {
                $this->processStudentLog($mapping['entity'], $firstScan, $lastScan, $date, $schoolId, $sorted);
            } else {
                $this->processStaffLog($mapping['entity'], $firstScan, $lastScan, $date, $schoolId, $sorted);
            }

            // Mark all logs as processed
            $pinLogs->each(fn($l) => $l->update(['is_processed' => true]));
            $processed += $pinLogs->count();
        }

        return [
            'total_logs' => $logs->count(),
            'processed' => $processed,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Proses log untuk siswa → Attendance.
     */
    protected function processStudentLog($student, $firstLog, $lastLog, string $date, string $schoolId, Collection $allLogs): void
    {
        $status = $this->determineStatus($firstLog->scan_time);
        $jamMasuk = config('app.jam_masuk', '07:00');

        // Cek apakah sudah ada absensi untuk tanggal ini
        $existing = Attendance::where('student_id', $student->id)
            ->whereDate('tanggal', $date)
            ->first();

        if ($existing) {
            // Update status jika diperlukan
            if ($existing->status === 'belum' || $status === 'terlambat') {
                $existing->update(['status' => $status]);
            }
            return;
        }

        // Cari semester aktif
        $semester = \App\Models\Semester::whereHas('academicYear', fn($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->first();

        if (!$semester) return;

        Attendance::create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'class_subject_id' => null,
            'tanggal' => $date,
            'status' => $status,
            'keterangan' => $status === 'terlambat' ? 'Telat ' . $firstLog->scan_time->format('H:i') : null,
            'created_by' => auth()->id() ?? $student->user_id,
        ]);
    }

    /**
     * Proses log untuk pegawai → StaffAttendance.
     */
    protected function processStaffLog($staff, $firstLog, $lastLog, string $date, string $schoolId, Collection $allLogs): void
    {
        $status = $this->determineStatus($firstLog->scan_time);
        $jamMasuk = config('app.jam_masuk', '07:00');

        $existing = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('tanggal', $date)
            ->first();

        if ($existing) {
            if (!$existing->check_out_time && $lastLog->scan_time->format('H:i:s') > '12:00:00') {
                $existing->update(['check_out_time' => $lastLog->scan_time->format('H:i:s')]);
            }
            return;
        }

        StaffAttendance::create([
            'staff_id' => $staff->id,
            'school_id' => $schoolId,
            'tanggal' => $date,
            'check_in_time' => $firstLog->scan_time->format('H:i:s'),
            'check_out_time' => ($lastLog && $lastLog->scan_time->format('H:i:s') > '12:00:00')
                ? $lastLog->scan_time->format('H:i:s')
                : null,
            'status' => $status,
            'source' => 'mesin_absen',
            'device_sn' => $firstLog->device->serial_number ?? null,
            'created_by' => auth()->id() ?? $staff->user_id,
        ]);
    }

    /**
     * Tentukan status hadir/terlambat berdasarkan jam tap-in.
     */
    protected function determineStatus(\DateTime $scanTime): string
    {
        $jamMasuk = config('app.jam_masuk', '07:00');
        $batasTelat = config('app.batas_telat', '07:30');

        $jam = $scanTime->format('H:i');

        if ($jam <= $batasTelat) {
            return 'hadir';
        }

        return 'terlambat';
    }

    // ─── PIN MAPPING MANAGEMENT ─────────────────────────────

    /**
     * Daftarkan mapping PIN → siswa/pegawai.
     */
    public function registerPin(string $schoolId, string $pin, string $entityType, string $entityId): FingerPinMapping
    {
        return FingerPinMapping::updateOrCreate(
            ['school_id' => $schoolId, 'pin' => $pin, 'entity_type' => $entityType],
            ['entity_id' => $entityId, 'is_active' => true]
        );
    }

    /**
     * Hapus mapping PIN.
     */
    public function unregisterPin(string $schoolId, string $pin): void
    {
        FingerPinMapping::where('school_id', $schoolId)
            ->where('pin', $pin)
            ->delete();
    }

    /**
     * Get all PIN mappings for a school.
     */
    public function getPinMappings(string $schoolId): Collection
    {
        return FingerPinMapping::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('entity_type')
            ->orderBy('pin')
            ->get();
    }

    // ─── DEVICE MANAGEMENT ──────────────────────────────────

    public function getDevices(string $schoolId): Collection
    {
        return FingerprintDevice::where('school_id', $schoolId)->orderBy('name')->get();
    }

    public function registerDevice(string $schoolId, array $data): FingerprintDevice
    {
        return FingerprintDevice::create(array_merge($data, ['school_id' => $schoolId]));
    }

    public function updateDevice(string $deviceId, array $data): FingerprintDevice
    {
        $device = FingerprintDevice::findOrFail($deviceId);
        $device->update($data);
        return $device;
    }

    public function deleteDevice(string $deviceId): void
    {
        FingerprintDevice::findOrFail($deviceId)->delete();
    }
}
