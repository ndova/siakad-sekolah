@extends('layouts.backend')

@section('title', 'Integrasi Dapodik')
@section('page_title', 'Integrasi Dapodik')

@section('content')
<div class="max-w-6xl mx-auto">

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-sm font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Ekspor Data --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i data-lucide="upload" class="w-4 h-4"></i> Ekspor Data ke Dapodik
        </h3>

        <form action="{{ route('dapodik.export') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Data</label>
                    <select name="entity_type" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                        <option value="student">Data Siswa & Rombel</option>
                        <option value="teacher">Penugasan Guru & Jadwal</option>
                        <option value="grade">Nilai & Rapor (Kurmer, PKL, P5)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label>
                    <select name="semester_id" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                        @foreach($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->academicYear->code ?? '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl text-sm hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2">
                        <i data-lucide="file-down" class="w-4 h-4"></i> Proses Ekspor
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Rombel</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($classes as $c)
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:border-indigo-300 cursor-pointer text-sm">
                        <input type="checkbox" name="class_ids[]" value="{{ $c->id }}" checked
                            class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-slate-600">{{ $c->code }}</span>
                        <span class="text-xs text-slate-400 ml-auto">{{ $c->tingkat }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    {{-- Import Data --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i data-lucide="download" class="w-4 h-4"></i> Import Data Master dari Dapodik
        </h3>
        <p class="text-xs text-slate-400 mb-4">Unggah file CSV hasil ekspor dari aplikasi Dapodik untuk sinkronisasi data master.</p>

        <form action="{{ route('dapodik.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <input type="file" name="file" accept=".csv"
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1">Format: CSV, maks: 10MB</p>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 bg-slate-700 text-white font-semibold rounded-xl text-sm hover:bg-slate-800 transition flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i> Upload & Import
                </button>
            </div>
        </form>
    </div>

    {{-- Log Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="scroll-text" class="w-4 h-4"></i> Log Sinkronisasi Terbaru
            </h3>
            <a href="{{ route('dapodik.logs') }}" class="text-xs text-indigo-600 hover:underline font-medium">Lihat Semua</a>
        </div>

        @if($recentLogs->isEmpty())
        <p class="text-sm text-slate-400 text-center py-6">Belum ada aktivitas sinkronisasi.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Arah</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Entitas</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="text-center py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Record</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Waktu</th>
                        <th class="text-left py-2 px-3 text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($recentLogs as $log)
                    <tr class="hover:bg-slate-50/30">
                        <td class="py-2.5 px-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $log->direction === 'export' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                {{ $log->direction }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">{{ $log->entity_type }}</td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                {{ $log->status === 'failed' ? 'bg-red-50 text-red-600' : '' }}
                                {{ $log->status === 'processing' ? 'bg-amber-50 text-amber-600' : '' }}
                                {{ $log->status === 'pending' ? 'bg-slate-50 text-slate-400' : '' }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-center text-slate-500">{{ $log->success_count }}/{{ $log->total_records }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-400">{{ $log->created_at?->diffForHumans() }}</td>
                        <td class="py-2.5 px-3">
                            @if($log->status === 'success' && $log->file_path)
                            <a href="{{ route('dapodik.download', ['path' => base64_encode($log->file_path)]) }}"
                                class="text-xs text-indigo-600 hover:underline font-medium">
                                <i data-lucide="download" class="w-3 h-3 inline"></i> Unduh
                            </a>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
