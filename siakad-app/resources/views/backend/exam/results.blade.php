@extends('layouts.backend')

@section('title', 'Hasil Ujian')
@section('page_title', 'Hasil Ujian')

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex items-end gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih Ujian</label><select name="exam_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"><option value="">Pilih</option>@foreach($exams as $e)<option value="{{ $e->id }}" {{ $examId==$e->id?'selected':'' }}>{{ $e->code }} - {{ $e->name }}</option>@endforeach</select></div>
    </div>
</form>

@if($examId)
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="font-semibold text-slate-800">Hasil Ujian</h3>
        <div class="flex gap-3 text-xs text-slate-400">
            <span>Total: {{ $results->count() }} peserta</span>
            @php
            $avg = $results->avg('score'); $pass = $results->filter(fn($r)=>($r->score??0)>=75)->count();
            @endphp
            <span>Rata²: {{ number_format($avg,1) }}</span>
            <span>Lulus: {{ $pass }}</span>
        </div>
    </div>
    <table class="w-full text-sm"><thead><tr class="bg-slate-50 text-left">
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">#</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Siswa</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">NIS</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Benar</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Nilai</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Status</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Waktu</th>
    </tr></thead>
    <tbody class="divide-y divide-slate-50">
    @forelse($results as $r)
    @php $sess = $sessions[$r->exam_session_id] ?? null; @endphp
    <tr class="hover:bg-slate-50/30">
        <td class="px-4 py-3 text-xs text-slate-400">{{ $loop->iteration }}</td>
        <td class="px-4 py-3 font-medium text-slate-800">{{ $r->student->nama_lengkap ?? '-' }}</td>
        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $r->student->nis ?? '-' }}</td>
        <td class="px-4 py-3 text-center font-mono">{{ $r->correct_count ?? '-' }}/{{ $r->total_questions ?? '-' }}</td>
        <td class="px-4 py-3 text-center">
            <span class="font-mono font-bold {{ ($r->score??0)>=75?'text-emerald-600':'text-red-500' }}">{{ number_format($r->score??0,1) }}</span>
        </td>
        <td class="px-4 py-3 text-center">
            <span class="px-2 py-1 text-xs rounded-full {{ ($r->score??0)>=75?'bg-emerald-50 text-emerald-600':'bg-red-50 text-red-600' }}">{{ ($r->score??0)>=75?'Lulus':'Remidi' }}</span>
        </td>
        <td class="px-4 py-3 text-xs text-slate-400">{{ $sess ? \Carbon\Carbon::parse($sess->submitted_at)->format('d/m H:i') : '-' }}</td>
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">Belum ada peserta ujian.</td></tr>
    @endforelse
    </tbody></table>
</div>
@else
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100"><i data-lucide="check-circle-2" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i><p class="text-slate-400">Pilih ujian untuk melihat hasil.</p></div>
@endif
@endsection
