<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FingerprintDevice;
use App\Models\FingerLog;
use App\Services\FingerprintService;
use Illuminate\Http\Request;

class FingerprintController extends Controller
{
    public function __construct(
        protected FingerprintService $fingerService = new FingerprintService(),
    ) {}

    // ─── DEVICE MANAGEMENT ──────────────────────────────────

    public function index()
    {
        $devices = $this->fingerService->getDevices($this->schoolId());
        $mappings = $this->fingerService->getPinMappings($this->schoolId());
        $recentLogs = FingerLog::with('device')
            ->whereHas('device', fn($q) => $q->where('school_id', $this->schoolId()))
            ->latest('scan_time')
            ->take(50)
            ->get();

        return view('backend.fingerprint.index', compact('devices', 'mappings', 'recentLogs'));
    }

    public function storeDevice(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'serial_number' => 'required|string|max:50|unique:fingerprint_devices',
            'ip_address' => 'nullable|string|max:45',
            'port' => 'nullable|integer',
            'model' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:200',
        ]);

        $this->fingerService->registerDevice($this->schoolId(), $validated);

        return back()->with('success', 'Perangkat fingerprint berhasil didaftarkan.');
    }

    public function updateDevice(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'port' => 'nullable|integer',
            'location' => 'nullable|string|max:200',
            'is_active' => 'nullable|boolean',
        ]);

        $this->fingerService->updateDevice($id, $validated);

        return back()->with('success', 'Perangkat berhasil diupdate.');
    }

    public function deleteDevice(string $id)
    {
        $this->fingerService->deleteDevice($id);
        return back()->with('success', 'Perangkat berhasil dihapus.');
    }

    // ─── UPLOAD LOG ─────────────────────────────────────────

    public function uploadLogForm()
    {
        $devices = $this->fingerService->getDevices($this->schoolId());
        return view('backend.fingerprint.upload', compact('devices'));
    }

    /**
     * Upload file log dari mesin fingerprint (CSV/TXT).
     * Format: PIN, Tanggal, Jam, VerifyMode, IOMode
     */
    public function uploadLog(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:fingerprint_devices,id',
            'file' => 'required|file|mimes:csv,txt,log|max:10240',
        ]);

        $device = FingerprintDevice::findOrFail($validated['device_id']);

        $handle = fopen($validated['file']->path(), 'r');
        $logs = [];
        $lineCount = 0;

        while (($line = fgetcsv($handle)) !== false) {
            $lineCount++;
            if (empty($line[0]) || count($line) < 2) continue;

            // Format: PIN, Date, Time, VerifyMode(optional), IOMode(optional)
            $pin = trim($line[0]);
            $dateTime = trim($line[1]) . ' ' . (trim($line[2] ?? '00:00:00'));

            try {
                $scanTime = \Carbon\Carbon::parse($dateTime);
            } catch (\Exception $e) {
                continue;
            }

            $logs[] = [
                'pin' => $pin,
                'scan_time' => $scanTime,
                'verify_mode' => $line[3] ?? 'fp',
                'io_mode' => $line[4] ?? 'in',
                'work_code' => (int) ($line[5] ?? 0),
                'raw_data' => $line,
            ];
        }
        fclose($handle);

        $count = $this->fingerService->storeRawLogs($device->id, $logs);

        return back()->with('success', "{$count} log dari {$lineCount} baris berhasil diimpor. Silakan proses menjadi absensi.");
    }

    // ─── PROCESS LOGS ───────────────────────────────────────

    public function processLogs(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'default_status' => 'nullable|in:hadir,terlambat',
        ]);

        $result = $this->fingerService->processLogs(
            $this->schoolId(),
            $validated['tanggal'],
            $validated['default_status'] ?? 'hadir'
        );

        $msg = "Diproses: {$result['processed']}, Dilewati: {$result['skipped']}";
        if (!empty($result['errors'])) {
            $msg .= '. Error: ' . implode('; ', array_slice($result['errors'], 0, 5));
        }

        return back()->with('success', $msg);
    }

    // ─── PIN MAPPING ────────────────────────────────────────

    public function storePinMapping(Request $request)
    {
        $validated = $request->validate([
            'pin' => 'required|string|max:30',
            'entity_type' => 'required|in:student,staff',
            'entity_id' => 'required|uuid',
        ]);

        $this->fingerService->registerPin(
            $this->schoolId(),
            $validated['pin'],
            $validated['entity_type'],
            $validated['entity_id']
        );

        return back()->with('success', "PIN {$validated['pin']} berhasil didaftarkan.");
    }

    public function deletePinMapping(string $id)
    {
        \App\Models\FingerPinMapping::findOrFail($id)->delete();
        return back()->with('success', 'Mapping PIN berhasil dihapus.');
    }

    // ─── API ENDPOINT (untuk push dari mesin) ───────────────

    /**
     * API endpoint untuk mesin fingerprint mengirim data log.
     * POST /api/v1/fingerprint/push
     */
    public function apiPush(Request $request)
    {
        $validated = $request->validate([
            'device_sn' => 'required|string|max:50',
            'logs' => 'required|array|min:1',
            'logs.*.pin' => 'required|string',
            'logs.*.scan_time' => 'required|date',
            'logs.*.verify_mode' => 'nullable|string',
            'logs.*.io_mode' => 'nullable|string',
        ]);

        $device = FingerprintDevice::where('serial_number', $validated['device_sn'])->first();
        if (!$device) {
            return response()->json(['message' => 'Device not registered'], 404);
        }

        $count = $this->fingerService->storeRawLogs($device->id, $validated['logs']);

        return response()->json([
            'success' => true,
            'records_saved' => $count,
        ]);
    }

    // ─── HELPER ─────────────────────────────────────────────

    protected function schoolId(): string
    {
        return \App\Services\SchoolService::get()?->id ?? '';
    }
}
