@extends('layouts.backend')
@section('title', 'Import Absensi dari Mesin — SIAKAD')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Import Data Absensi dari Mesin</h2>
        <p class="text-sm text-slate-500 mt-0.5">Upload file CSV dari mesin absensi fingerprint/face recognition</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-6 mb-6">
        <h3 class="font-semibold text-slate-800 mb-3">Format File CSV</h3>
        <div class="bg-slate-50 rounded-lg p-3 text-xs font-mono text-slate-600 mb-4">
            device_sn, nip, tanggal, jam_masuk, jam_pulang<br>
            FP001, 198802022011011002, 2025-05-23, 06:45:00, 15:30:00<br>
            FP001, 199105052015011005, 2025-05-23, 07:15:00, 16:00:00<br>
            FP001, 198903032012011003, 2025-05-23, 07:05:00, 14:45:00
        </div>
        <ul class="text-xs text-slate-500 space-y-1 list-disc list-inside mb-4">
            <li>device_sn: Serial number mesin absen (opsional, untuk tracking)</li>
            <li>nip: NIP pegawai (harus sudah terdaftar di data staff)</li>
            <li>tanggal: Format YYYY-MM-DD</li>
            <li>jam_masuk: Format HH:MM:SS (kosongkan jika tidak tap-in)</li>
            <li>jam_pulang: Format HH:MM:SS (kosongkan jika tidak tap-out)</li>
        </ul>
        <p class="text-xs text-amber-600">Status otomatis: hadir (tepat waktu), terlambat (>07:30), alfa (tidak tap-in)</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-6">
        <form method="POST" action="{{ route('staff.attendance.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Upload File CSV</label>
                <div class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:border-indigo-300 transition-colors cursor-pointer" id="dropZone">
                    <input type="file" name="file" accept=".csv,.txt" required
                           class="hidden" id="fileInput"
                           onchange="document.getElementById('fileName').textContent = this.files[0]?.name || 'Pilih file CSV'">
                    <div class="text-3xl mb-2">📂</div>
                    <p class="text-sm text-slate-600" id="fileName">Klik untuk pilih file CSV</p>
                    <p class="text-xs text-slate-400 mt-1">Maksimal 2MB</p>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-700">
                ⚠️ Import akan menimpa data absensi yang sudah ada untuk tanggal yang sama (update or create).
            </div>

            <button class="btn-primary w-full py-2.5 rounded-xl font-semibold">
                Mulai Import
            </button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('staff.attendance.grid') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Kembali ke Grid Absensi</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('dropZone').addEventListener('click', () => {
    document.getElementById('fileInput').click();
});

document.getElementById('dropZone').addEventListener('dragover', (e) => {
    e.preventDefault();
    e.currentTarget.classList.add('border-indigo-400', 'bg-indigo-50');
});

document.getElementById('dropZone').addEventListener('dragleave', (e) => {
    e.currentTarget.classList.remove('border-indigo-400', 'bg-indigo-50');
});

document.getElementById('dropZone').addEventListener('drop', (e) => {
    e.preventDefault();
    e.currentTarget.classList.remove('border-indigo-400', 'bg-indigo-50');
    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('fileInput').files = files;
        document.getElementById('fileName').textContent = files[0].name;
    }
});
</script>
@endpush
