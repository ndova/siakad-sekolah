@extends('layouts.backend')

@section('title', 'Rapor K13 — ' . $student->nama_lengkap)
@section('page_title', 'Rapor Kurikulum 2013')

@push('styles')
<style>
.rapor-wrapper { max-width: 210mm; margin: 0 auto; }
.rapor-header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; }
@media print {
    body { background: #fff !important; }
    .sidebar, .topbar, .rapor-actions { display: none !important; }
    .page-content { padding: 0 !important; margin: 0 !important; }
    .rapor-wrapper { max-width: 100%; box-shadow: none !important; }
    .rapor-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { size: A4; margin: 10mm; }
}
.predikat-A { background: #ecfdf5; color: #059669; }
.predikat-B { background: #eff6ff; color: #2563eb; }
.predikat-C { background: #fefce8; color: #ca8a04; }
.predikat-D { background: #fef2f2; color: #dc2626; }
</style>
@endpush

@section('content')

@php
    $tempatCetak = $school->tempat_cetak ?? '_______________';
    $raporTanggal = now()->locale('id')->translatedFormat('d F Y');
@endphp

<div class="flex items-center gap-3 mb-5 rapor-actions">
    <a href="{{ route('academic.reports', ['class_id'=>$class->id, 'semester_id'=>$semester->id]) }}"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
    </a>
    <button onclick="window.print()"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-medium">
        <i data-lucide="printer" class="w-4 h-4"></i> Cetak Rapor
    </button>
</div>

<div class="rapor-wrapper bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    {{-- HEADER --}}
    <div class="rapor-header px-8 py-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold shrink-0">
                    {{ strtoupper(substr($school->name ?? 'S', 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold">{{ $school->name ?? 'Nama Sekolah' }}</h2>
                    <p class="text-xs text-white/70 mt-0.5">{{ $school->address ?? '' }}</p>
                    <p class="text-xs text-white/70">NPSN: {{ $school->npsn ?? '-' }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-white/70 uppercase tracking-wide mb-2">Kurikulum 2013</div>
                <h1 class="text-2xl font-bold">RAPOR</h1>
                <p class="text-sm text-white/80 mt-1">{{ $semester->name }} — T.A {{ $semester->academicYear->code ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- IDENTITAS SISWA --}}
    <div class="px-8 py-5 border-b border-slate-100">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="block text-xs text-slate-400 uppercase font-semibold mb-0.5">Nama Peserta Didik</span>
                <span class="font-semibold text-slate-800">{{ $student->nama_lengkap }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-400 uppercase font-semibold mb-0.5">NISN / NIS</span>
                <span class="font-semibold text-slate-800">{{ $student->nisn }} / {{ $student->nis }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-400 uppercase font-semibold mb-0.5">Kelas</span>
                <span class="font-semibold text-slate-800">{{ $class->code ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-400 uppercase font-semibold mb-0.5">Tahun Ajaran</span>
                <span class="font-semibold text-slate-800">{{ $semester->academicYear->code ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- A. NILAI PENGETAHUAN & KETERAMPILAN (KI-3 & KI-4) --}}
    <div class="px-8 py-5 border-b border-slate-100">
        <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-bold">A</span>
            Nilai Pengetahuan & Keterampilan
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-slate-200">
                        <th class="text-left py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-8">No</th>
                        <th class="text-left py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase">Mata Pelajaran</th>
                        <th class="text-center py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-16">KKM</th>
                        <th class="text-center py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-20">Pengetahuan<br>(KI-3)</th>
                        <th class="text-center py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-20">Keterampilan<br>(KI-4)</th>
                        <th class="text-center py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-24">Predikat</th>
                        <th class="text-left py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @php $no = 1; @endphp
                    @foreach($mapelList as $mapel)
                    @php
                        $predikat = $mapel['predikat'] ?? '-';
                        $predikatClass = match($predikat) {
                            'A' => 'predikat-A', 'B' => 'predikat-B',
                            'C' => 'predikat-C', default => 'predikat-D',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/30">
                        <td class="py-2.5 px-2 text-slate-400 align-top">{{ $no++ }}</td>
                        <td class="py-2.5 px-2 font-medium text-slate-700 align-top">{{ $mapel['nama'] }}</td>
                        <td class="py-2.5 px-2 text-center font-bold text-slate-500 align-top">{{ $mapel['kkm'] ?? 70 }}</td>
                        <td class="py-2.5 px-2 text-center font-bold text-slate-800 align-top">{{ ($mapel['ki3'] ?? null) !== null ? number_format($mapel['ki3'], 0) : '-' }}</td>
                        <td class="py-2.5 px-2 text-center font-bold text-slate-800 align-top">{{ ($mapel['ki4'] ?? null) !== null ? number_format($mapel['ki4'], 0) : '-' }}</td>
                        <td class="py-2.5 px-2 text-center align-top">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $predikatClass }}">
                                {{ $predikat }}
                            </span>
                        </td>
                        <td class="py-2.5 px-2 text-slate-600 text-xs leading-relaxed align-top">
                            {{ $mapel['deskripsi'] ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- B. PRESENSI --}}
    <div class="px-8 py-5 border-b border-slate-100">
        <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 text-sm font-bold">B</span>
            Kehadiran
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $presensi = [
                    ['label'=>'Hadir','value'=>$attendanceRecap['hadir'],'icon'=>'check-circle','color'=>'text-emerald-600','bg'=>'bg-emerald-50'],
                    ['label'=>'Izin','value'=>$attendanceRecap['izin'],'icon'=>'file-text','color'=>'text-amber-600','bg'=>'bg-amber-50'],
                    ['label'=>'Sakit','value'=>$attendanceRecap['sakit'],'icon'=>'heart','color'=>'text-orange-600','bg'=>'bg-orange-50'],
                    ['label'=>'Alfa','value'=>$attendanceRecap['alfa'],'icon'=>'x-circle','color'=>'text-red-600','bg'=>'bg-red-50'],
                ];
            @endphp
            @foreach($presensi as $p)
            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 {{ $p['bg'] }}">
                <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center">
                    <i data-lucide="{{ $p['icon'] }}" class="w-5 h-5 {{ $p['color'] }}"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $p['color'] }}">{{ $p['value'] }}</p>
                    <p class="text-xs text-slate-500">{{ $p['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- C. CATATAN WALI KELAS --}}
    <div class="px-8 py-5 border-b border-slate-100">
        <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600 text-sm font-bold">C</span>
            Catatan Wali Kelas
        </h3>
        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 min-h-[60px] text-sm text-slate-600 italic">
            {{ $catatanWalikelas ?: 'Pertahankan dan tingkatkan prestasi belajar. Tetap semangat!' }}
        </div>
    </div>

    {{-- D. TANDA TANGAN --}}
    <div class="px-8 py-8">
        <div class="flex justify-between mb-14">
            {{-- Orang Tua/Wali --}}
            <div class="text-center w-52">
                <p class="text-xs text-white mb-1 select-none">—</p>
                <p class="text-sm font-semibold text-slate-700 mb-12">Orang Tua / Wali</p>
                <div class="mb-1">
                    <div class="border-b-2 border-slate-400 w-44 mx-auto h-8"></div>
                </div>
                <p class="text-xs font-semibold text-slate-700 mt-1">{{ $orangTua->nama_lengkap ?? '______________________' }}</p>
            </div>

            {{-- Wali Kelas --}}
            <div class="text-center w-52">
                <p class="text-xs text-slate-500 mb-1">{{ $tempatCetak }}, {{ $raporTanggal }}</p>
                <p class="text-sm font-semibold text-slate-700 mb-12">Wali Kelas</p>
                <div class="mb-1">
                    <div class="border-b-2 border-slate-400 w-44 mx-auto h-8"></div>
                </div>
                <p class="text-xs font-semibold text-slate-700 mt-1">{{ $waliKelas->name ?? '__________________' }}</p>
                @if($waliKelas->nip ?? false)
                <p class="text-xs text-slate-400 mt-0.5">NIP. {{ $waliKelas->nip }}</p>
                @endif
            </div>
        </div>

        {{-- Kepala Sekolah --}}
        <div class="flex justify-center">
            <div class="text-center w-52">
                <p class="text-xs text-slate-500 mb-1">Mengetahui,</p>
                <p class="text-sm font-semibold text-slate-700 mb-10">Kepala Sekolah</p>
                <div class="mb-1">
                    <div class="border-b-2 border-slate-400 w-44 mx-auto h-8"></div>
                </div>
                <p class="text-xs font-semibold text-slate-700 mt-1">{{ $kepalaSekolah->nama_lengkap ?? '______________________' }}</p>
                @if($kepalaSekolah->nip ?? false)
                <p class="text-xs text-slate-400 mt-0.5">NIP. {{ $kepalaSekolah->nip }}</p>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
