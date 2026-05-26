@extends('layouts.backend')

@section('title', 'Import Dapodik')
@section('page_title', 'Import Data dari Dapodik')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Upload File CSV Dapodik</h3>

        <form action="{{ route('dapodik.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">File CSV</label>
                <input type="file" name="file" accept=".csv" required
                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                <p class="text-xs text-slate-400 mt-2">Format: CSV hasil ekspor dari aplikasi Dapodik.</p>
            </div>

            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-amber-700 mb-2">Panduan Import</h4>
                <ul class="text-xs text-amber-600 space-y-1 list-disc list-inside">
                    <li>Pastikan file CSV sesuai format Dapodik resmi</li>
                    <li>Data yang sudah ada (berdasarkan NISN/NUPTK/kode) akan di-update</li>
                    <li>Data baru akan otomatis dibuatkan mapping</li>
                    <li>Backup database sebelum menjalankan import</li>
                </ul>
            </div>

            <button type="submit"
                class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl text-sm hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                <i data-lucide="upload" class="w-4 h-4"></i> Upload & Proses Import
            </button>
        </form>
    </div>
</div>
@endsection
