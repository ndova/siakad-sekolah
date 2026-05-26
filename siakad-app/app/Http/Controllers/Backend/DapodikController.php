<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\ExportDapodikJob;
use App\Models\SyncLog;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\DapodikMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DapodikController extends Controller
{
    // ─── HALAMAN UTAMA ──────────────────────────────────────

    public function index()
    {
        $classes = SchoolClass::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $semesters = Semester::with('academicYear')
            ->whereHas('academicYear', fn($q) => $q->where('school_id', $this->schoolId()))
            ->orderByDesc('start_date')
            ->get();

        $recentLogs = SyncLog::latest()->take(20)->get();

        return view('backend.dapodik.index', compact('classes', 'semesters', 'recentLogs'));
    }

    // ─── EKSPOR ─────────────────────────────────────────────

    public function export(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|in:student,teacher,grade',
            'class_ids'   => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        ExportDapodikJob::dispatch(
            $validated['entity_type'],
            collect($validated['class_ids']),
            $validated['semester_id'],
            auth()->id(),
        );

        return redirect()->route('dapodik.index')
            ->with('success', 'Ekspor ' . $validated['entity_type'] . ' sedang diproses. Lihat status di Log Sinkronisasi.');
    }

    // ─── DOWNLOAD ───────────────────────────────────────────

    public function download(Request $request)
    {
        $path = base64_decode($request->get('path'));
        abort_if(!Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    // ─── STATUS ─────────────────────────────────────────────

    public function status()
    {
        $latestLog = SyncLog::where('direction', 'export')
            ->latest()
            ->first();

        return response()->json([
            'status' => $latestLog?->status ?? 'idle',
            'progress' => $latestLog
                ? "{$latestLog->success_count}/{$latestLog->total_records}"
                : '0/0',
            'download_url' => ($latestLog?->status === 'success' && $latestLog->file_path)
                ? route('dapodik.download', ['path' => base64_encode($latestLog->file_path)])
                : null,
        ]);
    }

    // ─── IMPORT ─────────────────────────────────────────────

    public function importForm()
    {
        return view('backend.dapodik.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        // Simpan file
        $path = $request->file('file')->store('imports/dapodik');

        // TODO: Proses import via Job
        return redirect()->route('dapodik.index')
            ->with('success', 'File berhasil diunggah. Fitur import akan segera tersedia.');
    }

    // ─── MAPPING ────────────────────────────────────────────

    public function mappings(Request $request)
    {
        $entityType = $request->get('entity_type', 'subject');

        $mappings = DapodikMapping::where('entity_type', $entityType)
            ->latest('updated_at')
            ->paginate(25);

        return view('backend.dapodik.mappings', compact('mappings', 'entityType'));
    }

    public function updateMapping(Request $request)
    {
        $validated = $request->validate([
            'entity_type'   => 'required|string|max:50',
            'local_id'      => 'required|uuid',
            'dapodik_id'    => 'nullable|string|max:100',
            'dapodik_code'  => 'nullable|string|max:50',
        ]);

        DapodikMapping::setMapping(
            $validated['entity_type'],
            $validated['local_id'],
            $validated['dapodik_id'],
            $validated['dapodik_code'],
        );

        return back()->with('success', 'Mapping berhasil disimpan.');
    }

    // ─── LOG ────────────────────────────────────────────────

    public function logs()
    {
        $logs = SyncLog::latest()->paginate(50);
        return view('backend.dapodik.logs', compact('logs'));
    }

    // ─── HELPER ─────────────────────────────────────────────

    protected function schoolId(): string
    {
        return \App\Services\SchoolService::get()?->id ?? '';
    }
}
