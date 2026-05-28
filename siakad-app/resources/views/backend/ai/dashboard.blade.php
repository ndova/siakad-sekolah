@extends('layouts.backend')

@section('title', 'AI Analitik')
@section('page_title', 'AI Analitik')

@section('content')
{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
        <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
    </div>
    <div>
        <h2 class="text-lg font-semibold text-slate-800">Analitik Cerdas</h2>
        <p class="text-xs text-slate-400">Insight dan rekomendasi berbasis data akademik</p>
    </div>
</div>

{{-- Performa per Kelas --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
        <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Performa Rata-rata per Kelas
    </h3>
    @if($kelasPerform->isEmpty())
    <div class="bg-white rounded-2xl p-6 border border-slate-100 text-center text-slate-400 text-sm">Belum ada data nilai untuk dianalisis.</div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($kelasPerform as $kp)
        @php
            $barColor = $kp->avg_nilai >= 80 ? 'bg-emerald-500' : ($kp->avg_nilai >= 70 ? 'bg-amber-500' : 'bg-red-500');
            $textColor = $kp->avg_nilai >= 80 ? 'text-emerald-600' : ($kp->avg_nilai >= 70 ? 'text-amber-600' : 'text-red-600');
        @endphp
        <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:shadow-sm transition">
            <div class="flex items-center justify-between mb-3">
                <span class="font-semibold text-slate-800">{{ $kp->code }}</span>
                <span class="text-xs text-slate-400">{{ $kp->total_siswa }} siswa</span>
            </div>
            <div class="flex items-end gap-3">
                <span class="text-3xl font-bold {{ $textColor }}">{{ number_format($kp->avg_nilai, 1) }}</span>
                <span class="text-xs text-slate-400 mb-1.5">rata-rata</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-2">
                <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ min($kp->avg_nilai, 100) }}%"></div>
            </div>
            @if($kp->siswa_berisiko > 0)
            <div class="mt-2 flex items-center gap-1 text-xs text-red-500">
                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                <span>{{ $kp->siswa_berisiko }} siswa perlu perhatian</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Grid: Siswa Berisiko + Analisis Mapel --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- Siswa Perlu Perhatian --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-2">
            <i data-lucide="heart-pulse" class="w-4 h-4 text-red-500"></i>
            <h3 class="text-sm font-semibold text-slate-700">Siswa Perlu Perhatian</h3>
            @if($siswaBerisiko->isNotEmpty())
            <span class="ml-auto px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">{{ $siswaBerisiko->count() }} siswa</span>
            @endif
        </div>
        <div class="p-4">
            @if($siswaBerisiko->isEmpty())
            <p class="text-sm text-slate-400 text-center py-4">✅ Semua siswa dalam performa baik</p>
            @else
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($siswaBerisiko as $sb)
                <div class="p-3 rounded-xl border border-red-100 bg-red-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="font-medium text-slate-800 text-sm">{{ $sb->nama }}</span>
                            <span class="text-xs text-slate-400 ml-2">{{ $sb->kelas }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sb->avg_nilai < 65 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ number_format($sb->avg_nilai, 1) }}
                        </span>
                    </div>
                    <div class="flex gap-3 text-xs text-slate-500 mb-2">
                        <span>🔴 Alfa: {{ $sb->alfa }}x</span>
                        <span>📊 Tren: {{ $sb->trend }}</span>
                    </div>
                    <div class="text-xs text-slate-600 whitespace-pre-line leading-relaxed">{{ $sb->rekomendasi }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Analisis per Mapel --}}
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-2">
            <i data-lucide="book-open" class="w-4 h-4 text-blue-500"></i>
            <h3 class="text-sm font-semibold text-slate-700">Analisis per Mata Pelajaran</h3>
        </div>
        <div class="p-4">
            @if($mapelAnalisis->isEmpty())
            <p class="text-sm text-slate-400 text-center py-4">Belum ada data mapel.</p>
            @else
            <div class="space-y-3">
                @foreach($mapelAnalisis as $ma)
                @php
                    $mBarColor = $ma->avg_nilai >= 80 ? 'bg-emerald-500' : ($ma->avg_nilai >= 70 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-slate-700">{{ $ma->nama }}</span>
                        <span class="text-xs font-medium {{ $ma->avg_nilai >= 75 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($ma->avg_nilai, 1) }}
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="{{ $mBarColor }} h-1.5 rounded-full" style="width: {{ min($ma->avg_nilai, 100) }}%"></div>
                    </div>
                    @if($ma->pct_below > 20)
                    <div class="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $ma->below_kkm }} dari {{ $ma->total_nilai }} nilai di bawah KKM ({{ $ma->pct_below }}%)
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- TP/KD yang Perlu Diulang --}}
@if($tpRendah->isNotEmpty())
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-100 flex items-center gap-2">
        <i data-lucide="refresh-cw" class="w-4 h-4 text-orange-500"></i>
        <h3 class="text-sm font-semibold text-slate-700">Rekomendasi TP/KD yang Perlu Diulang</h3>
        <span class="ml-auto text-xs text-slate-400">Banyak siswa mendapat nilai &lt; 65</span>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($tpRendah as $tp)
            <div class="p-3 rounded-xl border border-orange-100 bg-orange-50/50">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="book" class="w-4 h-4 text-orange-500"></i>
                    <span class="font-medium text-sm text-slate-700">{{ $tp->mapel }}</span>
                    <span class="ml-auto px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">{{ $tp->total_rendah }} rendah</span>
                </div>
                @if($tp->tp_list->isNotEmpty())
                <ul class="space-y-1">
                    @foreach($tp->tp_list as $desc)
                    <li class="text-xs text-slate-600 flex items-start gap-1.5">
                        <span class="text-orange-400 mt-0.5">•</span>
                        <span>{{ \Illuminate\Support\Str::limit($desc, 80) }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Rekomendasi Cerdas --}}
@if($siswaBerisiko->isNotEmpty() || $tpRendah->isNotEmpty())
<div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl border border-violet-100 p-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center">
            <i data-lucide="lightbulb" class="w-5 h-5 text-violet-600"></i>
        </div>
        <h3 class="font-semibold text-slate-800">Ringkasan Rekomendasi AI</h3>
    </div>
    <div class="space-y-2 text-sm text-slate-600">
        @php
            $totalBerisiko = $siswaBerisiko->count();
            $mapelRendah = $mapelAnalisis->filter(fn($m) => $m->avg_nilai < 75)->count();
        @endphp
        @if($totalBerisiko > 0)
        <div class="flex items-start gap-2">
            <span class="text-red-500 mt-0.5">⚠️</span>
            <span>Terdeteksi <strong>{{ $totalBerisiko }} siswa</strong> yang memerlukan perhatian khusus karena nilai rendah dan/atau kehadiran bermasalah.</span>
        </div>
        @endif
        @if($mapelRendah > 0)
        <div class="flex items-start gap-2">
            <span class="text-amber-500 mt-0.5">📊</span>
            <span>Ada <strong>{{ $mapelRendah }} mata pelajaran</strong> dengan rata-rata di bawah 75. Pertimbangkan remedial atau pengayaan.</span>
        </div>
        @endif
        @if($tpRendah->isNotEmpty())
        <div class="flex items-start gap-2">
            <span class="text-orange-500 mt-0.5">🔄</span>
            <span>Sebanyak <strong>{{ $tpRendah->sum('total_rendah') }} capaian pembelajaran (TP/KD)</strong> perlu diulang karena banyak siswa belum tuntas.</span>
        </div>
        @endif
        <div class="flex items-start gap-2 mt-3 pt-3 border-t border-violet-200">
            <span class="text-violet-500 mt-0.5">💡</span>
            <span class="text-violet-700">Gunakan data ini untuk menyusun program remedial, bimbingan belajar, atau evaluasi metode pengajaran.</span>
        </div>
    </div>
</div>
@endif
@endsection
