@extends('layouts.backend')

@section('title', 'Rekap Presensi')
@section('page_title', 'Rekap Presensi')

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border-accent-100 text-accent text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

{{-- Filter --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label>
            <select name="class_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                <option value="">Pilih Kelas</option>
                @foreach($classes as $k)<option value="{{ $k->id }}" {{ $classId==$k->id?'selected':'' }}>{{ $k->code }} · {{ $k->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan</label>
            <select name="month" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month==$m?'selected':'' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
            <input type="number" name="year" value="{{ $year }}" min="2020" max="2099" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200 w-28">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl btn-accent text-white text-sm font-medium flex items-center gap-1.5"><i data-lucide="search" class="w-4 h-4"></i> Tampilkan</button>
        <a href="{{ route('academic.attendance') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-sm text-slate-600 hover:bg-slate-200 flex items-center gap-1.5"><i data-lucide="arrow-left" class="w-4 h-4"></i> Input Presensi</a>
    </div>
</form>

@if($class && $recapData->isNotEmpty())
{{-- Ringkasan Kelas --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
    @php
        $clsHadir = $recapData->sum('hadir');
        $clsIzin = $recapData->sum('izin');
        $clsSakit = $recapData->sum('sakit');
        $clsAlfa = $recapData->sum('alfa');
        $clsTerlambat = $recapData->sum('terlambat');
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 p-4 text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $clsHadir }}</div><div class="text-xs text-slate-400 mt-1">✅ Hadir</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-4 text-center">
        <div class="text-2xl font-bold text-yellow-600">{{ $clsTerlambat }}</div><div class="text-xs text-slate-400 mt-1">⏰ Terlambat</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-4 text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $clsIzin }}</div><div class="text-xs text-slate-400 mt-1">📝 Izin</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-4 text-center">
        <div class="text-2xl font-bold text-orange-600">{{ $clsSakit }}</div><div class="text-xs text-slate-400 mt-1">🏥 Sakit</div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-4 text-center">
        <div class="text-2xl font-bold text-red-600">{{ $clsAlfa }}</div><div class="text-xs text-slate-400 mt-1">❌ Alfa</div>
    </div>
</div>

{{-- Info Kelas --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-5 flex flex-wrap justify-between items-center gap-3">
    <div>
        <h3 class="font-semibold text-slate-800">{{ $class->code }} - {{ $class->name }}</h3>
        <p class="text-xs text-slate-400 mt-0.5">
            Wali Kelas: {{ $class->waliKelas->name ?? '-' }} &middot;
            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }} &middot;
            {{ $semester->academicYear->year_label ?? '' }} - S{{ $semester->semester_number ?? '' }}
        </p>
    </div>
    @php
        $totalAll = $clsHadir + $clsIzin + $clsSakit + $clsAlfa + $clsTerlambat;
        $pctKelas = $totalAll > 0 ? round((($clsHadir + $clsTerlambat) / $totalAll) * 100, 1) : 0;
    @endphp
    <div class="text-right">
        <div class="text-xs text-slate-400">Tingkat Kehadiran Kelas</div>
        <div class="text-2xl font-bold {{ $pctKelas >= 90 ? 'text-emerald-600' : ($pctKelas >= 75 ? 'text-amber-600' : 'text-red-600') }}">{{ $pctKelas }}%</div>
    </div>
</div>

{{-- Tabel Rekap --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">H</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">T</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">I</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">S</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">A</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Total</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">% Hadir</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
        @foreach($recapData as $r)
        @php
            $colorClass = $r->persentase >= 90 ? 'text-emerald-600' : ($r->persentase >= 75 ? 'text-amber-600' : 'text-red-600');
        @endphp
        <tr class="hover:bg-slate-50/50">
            <td class="px-4 py-3 font-mono text-sm">{{ $r->nis }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">{{ $r->nama }}</td>
            <td class="px-4 py-3 text-center text-xs font-semibold text-emerald-600">{{ $r->hadir }}</td>
            <td class="px-4 py-3 text-center text-xs font-semibold text-yellow-600">{{ $r->terlambat }}</td>
            <td class="px-4 py-3 text-center text-xs font-semibold text-amber-600">{{ $r->izin }}</td>
            <td class="px-4 py-3 text-center text-xs font-semibold text-orange-600">{{ $r->sakit }}</td>
            <td class="px-4 py-3 text-center text-xs font-semibold text-red-600">{{ $r->alfa }}</td>
            <td class="px-4 py-3 text-center text-xs font-semibold text-slate-600">{{ $r->total }}</td>
            <td class="px-4 py-3 text-center text-xs font-bold {{ $colorClass }}">{{ $r->persentase }}%</td>
        </tr>
        @endforeach
        </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
</div>
@else
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Pilih kelas, bulan, dan tahun untuk melihat rekap presensi.</p>
</div>
@endif
@endsection
