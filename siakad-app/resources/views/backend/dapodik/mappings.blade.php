@extends('layouts.backend')

@section('title', 'Mapping Kode Dapodik')
@section('page_title', 'Mapping Kode Dapodik')

@section('content')
<div class="max-w-5xl mx-auto">

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-sm font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b">
            <h3 class="font-semibold text-slate-800 mb-3">Mapping Kode Internal → Dapodik</h3>
            <p class="text-xs text-slate-400 mb-4">
                Mapping ini menghubungkan ID internal SIAKAD dengan kode referensi Dapodik.
                Isi kode Dapodik yang sesuai untuk setiap entitas agar data ekspor valid.
            </p>

            {{-- Filter entity type --}}
            <div class="flex gap-2 flex-wrap">
                @php
                $types = [
                    'subject' => 'Mata Pelajaran',
                    'class' => 'Rombel',
                    'major' => 'Program Keahlian',
                    'specialization' => 'Konsentrasi',
                    'student' => 'Siswa',
                    'teacher' => 'Guru',
                    'p5_project' => 'Projek P5',
                ];
                @endphp
                @foreach($types as $key => $label)
                <a href="?entity_type={{ $key }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                    {{ $entityType === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>

        @if($mappings->isEmpty())
        <p class="text-sm text-slate-400 text-center py-12">
            Belum ada mapping untuk tipe "{{ $entityType }}".
            Mapping akan otomatis dibuat saat data diimpor dari Dapodik.
        </p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Local ID</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Kode Dapodik</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">ID Dapodik</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Terakhir Sinkron</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($mappings as $m)
                    <tr class="hover:bg-slate-50/30">
                        <td class="py-3 px-4 font-mono text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($m->local_id, 16) }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-xs font-mono">
                                {{ $m->dapodik_code ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-slate-400">{{ $m->dapodik_id ?? '—' }}</td>
                        <td class="py-3 px-4 text-xs text-slate-400">{{ $m->last_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $mappings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
