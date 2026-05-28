@extends('layouts.backend')

@section('title', 'AI Prediksi & Klasifikasi')
@section('page_title', 'Prediksi & Klasifikasi Siswa')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
        <i data-lucide="brain-circuit" class="w-5 h-5 text-white"></i>
    </div>
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Prediksi & Klasifikasi AI</h2>
        <p class="text-xs text-slate-400">Klasifikasi otomatis performa siswa berdasarkan nilai, tren, dan kehadiran</p>
    </div>
</div>

{{-- Ringkasan Klasifikasi --}}
@if($ringkasan['total'] > 0)
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
        <div class="text-2xl font-bold text-slate-800">{{ $ringkasan['total'] }}</div>
        <div class="text-xs text-slate-400 mt-1">Total Siswa</div>
    </div>
    <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100 text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $ringkasan['sangat_baik'] }}</div>
        <div class="text-xs text-emerald-600 mt-1">🌟 Sangat Baik</div>
    </div>
    <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100 text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $ringkasan['baik'] }}</div>
        <div class="text-xs text-blue-600 mt-1">👍 Baik</div>
    </div>
    <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100 text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $ringkasan['cukup'] }}</div>
        <div class="text-xs text-amber-600 mt-1">📝 Cukup</div>
    </div>
    <div class="bg-red-50 rounded-2xl p-4 border border-red-100 text-center">
        <div class="text-2xl font-bold text-red-600">{{ $ringkasan['perlu_bimbingan'] + $ringkasan['berisiko'] }}</div>
        <div class="text-xs text-red-600 mt-1">⚠️ Perlu Perhatian</div>
    </div>
</div>
@endif

{{-- Tabel Klasifikasi --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 text-left">
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Rata² Nilai</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Alfa</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Tren</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Klasifikasi AI</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Rekomendasi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($klasifikasi as $k)
            <tr class="hover:bg-slate-50/50 transition">
                <td class="px-5 py-3 font-medium text-slate-800">{{ $k->nama }}</td>
                <td class="px-5 py-3 text-slate-500">{{ $k->kelas }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="font-medium {{ $k->avg_nilai >= 75 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($k->avg_nilai, 1) }}</span>
                </td>
                <td class="px-5 py-3 text-center {{ $k->alfa > 3 ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $k->alfa }}</td>
                <td class="px-5 py-3 text-center">
                    @if($k->trend == 'naik')
                        <span class="text-emerald-600 text-xs">📈 Naik</span>
                    @elseif($k->trend == 'menurun')
                        <span class="text-red-600 text-xs">📉 Turun</span>
                    @else
                        <span class="text-slate-400 text-xs">➡️ Stabil</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @php
                        $warnaMap = [
                            'emerald' => 'bg-emerald-50 text-emerald-700',
                            'blue' => 'bg-blue-50 text-blue-700',
                            'amber' => 'bg-amber-50 text-amber-700',
                            'orange' => 'bg-orange-50 text-orange-700',
                            'red' => 'bg-red-50 text-red-700',
                        ];
                        $warnaClass = $warnaMap[$k->warna] ?? 'bg-slate-50 text-slate-700';
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $warnaClass }}">{{ $k->kategori }}</span>
                </td>
                <td class="px-5 py-3 text-xs text-slate-500 max-w-xs">{{ $k->rekomendasi }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada data untuk dianalisis.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Info Footer --}}
<div class="mt-6 p-5 rounded-2xl bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200">
    <h4 class="text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2"><i data-lucide="info" class="w-4 h-4 text-slate-500"></i> Metodologi Klasifikasi</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-500">
        <div><strong class="text-slate-700">Rata-rata Nilai:</strong> Rerata semua nilai per siswa di semester aktif</div>
        <div><strong class="text-slate-700">Tren:</strong> Perbandingan nilai awal vs akhir semester</div>
        <div><strong class="text-slate-700">Kehadiran:</strong> Persentase ketidakhadiran tanpa keterangan</div>
    </div>
</div>
@endsection
