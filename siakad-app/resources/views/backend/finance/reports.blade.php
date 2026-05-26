@extends('layouts.backend')

@section('title', 'Laporan Keuangan')
@section('page_title', 'Laporan Keuangan')

@section('content')
<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex flex-wrap items-end gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan</label><select name="month" class="px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@for($i=1;$i<=12;$i++)<option value="{{ str_pad($i,2,'0',STR_PAD_LEFT) }}" {{ $month==str_pad($i,2,'0',STR_PAD_LEFT)?'selected':'' }}>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>@endfor</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label><select name="year" class="px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@for($y=now()->year-3;$y<=now()->year+1;$y++)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor</select></div>
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Tampilkan</button>
    </div>
</form>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-100"><div class="text-xs text-slate-400 mb-1">Pemasukan</div><div class="text-2xl font-bold text-emerald-600">Rp{{ number_format($totalRevenue,0,',','.') }}</div><div class="text-[10px] text-slate-400 mt-1">{{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }} {{ $year }}</div></div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100"><div class="text-xs text-slate-400 mb-1">Total Tunggakan</div><div class="text-2xl font-bold text-red-500">Rp{{ number_format($totalUnpaid,0,',','.') }}</div></div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100"><div class="text-xs text-slate-400 mb-1">Koleksi / Tahun</div><div class="text-2xl font-bold text-sky-600">Rp{{ number_format(array_sum($monthlyData->toArray()),0,',','.') }}</div><div class="text-[10px] text-slate-400 mt-1">{{ $year }}</div></div>
</div>

{{-- Monthly Bar Chart --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-6">
    <h3 class="font-semibold text-slate-800 mb-4">Pemasukan Bulanan — {{ $year }}</h3>
    <div class="space-y-2">
    @php $maxVal = max($monthlyData->max()?:1, 1); @endphp
    @for($i=1;$i<=12;$i++)
    @php $val = $monthlyData[str_pad($i,2,'0',STR_PAD_LEFT)] ?? 0; $pct = round(($val/$maxVal)*100); @endphp
    <div class="flex items-center gap-3">
        <span class="text-xs text-slate-400 w-8 text-right">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('M') }}</span>
        <div class="flex-1 bg-slate-100 rounded-full h-5 overflow-hidden"><div class="h-full rounded-full {{ $pct>0 ? ($i==(int)$month?'bg-emerald-500':'bg-indigo-400') : '' }}" style="width:{{ $pct }}%"></div></div>
        <span class="text-xs font-mono text-slate-600 w-24 text-right">Rp{{ number_format($val,0,',','.') }}</span>
    </div>
    @endfor
    </div>
</div>

{{-- By Category --}}
@if($byCategory->count())
<div class="bg-white rounded-2xl border border-slate-100 p-5">
    <h3 class="font-semibold text-slate-800 mb-4">Pemasukan per Kategori</h3>
    <div class="space-y-2">
    @php $catMax = max($byCategory->max()?:1, 1); @endphp
    @foreach($byCategory as $ftId => $total)
    @php $ft = \App\Models\FeeType::find($ftId); $catPct = round(($total/$catMax)*100); @endphp
    <div class="flex items-center gap-3">
        <span class="text-xs text-slate-500 w-32 truncate">{{ $ft->name ?? 'ID:'.$ftId }}</span>
        <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden"><div class="h-full rounded-full bg-violet-400" style="width:{{ $catPct }}%"></div></div>
        <span class="text-xs font-mono font-semibold text-slate-700 w-28 text-right">Rp{{ number_format($total,0,',','.') }}</span>
    </div>
    @endforeach
    </div>
</div>
@endif
@endsection
