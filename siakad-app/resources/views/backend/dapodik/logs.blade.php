@extends('layouts.backend')

@section('title', 'Log Sinkronisasi — Dapodik')
@section('page_title', 'Log Sinkronisasi Dapodik')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">Riwayat Sinkronisasi</h3>
            <a href="{{ route('dapodik.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← Kembali ke Integrasi
            </a>
        </div>

        @if($logs->isEmpty())
        <p class="text-sm text-slate-400 text-center py-12">Belum ada log sinkronisasi.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Arah</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Entitas</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Berhasil</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Error</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Waktu Mulai</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Selesai</th>
                        <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">File</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($logs as $log)
                    <tr class="hover:bg-slate-50/30">
                        <td class="py-3 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $log->direction === 'export' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                {{ $log->direction }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $log->entity_type }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                {{ $log->status === 'failed' ? 'bg-red-50 text-red-600' : '' }}
                                {{ $log->status === 'processing' ? 'bg-amber-50 text-amber-600' : '' }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-emerald-600">{{ $log->success_count }}</td>
                        <td class="py-3 px-4 text-center font-mono {{ $log->error_count > 0 ? 'text-red-500' : 'text-slate-400' }}">{{ $log->error_count }}</td>
                        <td class="py-3 px-4 text-xs text-slate-400">{{ $log->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="py-3 px-4 text-xs text-slate-400">{{ $log->completed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($log->status === 'success' && $log->file_path)
                            <a href="{{ route('dapodik.download', ['path' => base64_encode($log->file_path)]) }}"
                                class="text-xs text-indigo-600 hover:underline font-medium flex items-center justify-center gap-1">
                                <i data-lucide="download" class="w-3 h-3"></i> CSV
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

        <div class="p-4 border-t">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
