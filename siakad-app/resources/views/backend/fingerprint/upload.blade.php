@extends('layouts.backend')

@section('title', 'Upload Log Fingerprint')
@section('page_title', 'Upload Log Fingerprint')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Upload File Log dari Mesin</h3>

        <form action="{{ route('fingerprint.log.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Perangkat</label>
                <select name="device_id" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                    <option value="">-- Pilih Perangkat --</option>
                    @foreach($devices as $d)
                    <option value="{{ $d->id }}">{{ $d->name }} (SN: {{ $d->serial_number }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">File Log</label>
                <input type="file" name="file" accept=".csv,.txt,.log" required
                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-slate-400 mt-1">Format CSV: PIN, Tanggal(YYYY-MM-DD), Jam(HH:MM:SS), VerifyMode, IOMode</p>
            </div>

            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-amber-700 mb-2">Format File</h4>
                <pre class="text-xs text-amber-600 overflow-x-auto">1001,2026-05-25,07:15:30,fp,in
1002,2026-05-25,07:20:45,fp,in
1001,2026-05-25,15:30:10,fp,out</pre>
                <p class="text-xs text-amber-500 mt-2">Kolom: PIN, Tanggal, Jam, VerifyMode(opsional), IOMode(opsional)</p>
            </div>

            <button type="submit"
                class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl text-sm hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                <i data-lucide="upload" class="w-4 h-4"></i> Upload & Simpan
            </button>
        </form>
    </div>
</div>
@endsection
