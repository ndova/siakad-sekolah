@extends('layouts.backend')

@section('title', 'Rapor — ' . $student->nama_lengkap)
@section('page_title', 'Rapor Kurikulum Merdeka')

@php
// ─── Build ordered modules array from school settings ───
$raporModules = [];
$moduleData = [
    'identitas' => ['view' => 'backend.academic._rapor_identitas', 'show' => true],
    'nilai'     => ['view' => 'backend.academic._rapor_nilai',     'show' => $school->rapor_show_nilai ?? true],
    'p5'        => ['view' => 'backend.academic._rapor_p5',        'show' => $school->rapor_show_p5 ?? true],
    'presensi'  => ['view' => 'backend.academic._rapor_presensi',  'show' => $school->rapor_show_presensi ?? true],
    'catatan'   => ['view' => 'backend.academic._rapor_catatan',   'show' => $school->rapor_show_catatan ?? true],
    'ttd'       => ['view' => 'backend.academic._rapor_ttd',       'show' => true],
];
foreach ($moduleData as $key => $cfg) {
    $orderField = 'rapor_order_' . $key;
    $raporModules[] = [
        'key'  => $key,
        'view' => $cfg['view'],
        'show' => $cfg['show'],
        'order'=> (int) ($school->{$orderField} ?? 0),
    ];
}
usort($raporModules, fn($a, $b) => $a['order'] <=> $b['order']);

// ─── Tanggal cetak rapor ───
if ($school->rapor_tgl_otomatis ?? true) {
    $raporTanggal = now()->locale('id')->translatedFormat('d F Y');
} else {
    $t = $school->rapor_tanggal;
    $b = $school->rapor_bulan;
    $y = $school->rapor_tahun;
    $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $raporTanggal = ($t ?: '__') . ' ' . ($b ? $bulanNames[$b] : '______') . ' ' . ($y ?: '____');
}
$tempatCetak = $school->tempat_cetak ?? '_______________';
@endphp

@push('styles')
<style>
.rapor-wrapper { max-width: 210mm; margin: 0 auto; }
.rapor-header { background: linear-gradient(135deg, var(--accent), color-mix(in srgb, var(--accent) 70%, #1e293b)); color: #fff; }

@media print {
    body { background: #fff !important; }
    .sidebar, .topbar, .rapor-actions { display: none !important; }
    .page-content { padding: 0 !important; margin: 0 !important; }
    .rapor-wrapper { max-width: 100%; box-shadow: none !important; }
    .rapor-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { size: A4; margin: 10mm; }
}

.tp-table { display: none; }
.tp-table.open { display: table; }
.tp-toggle { cursor: pointer; transition: all 0.15s; }
.tp-toggle:hover { color: var(--accent); }
.predikat-A { background: #ecfdf5; color: #059669; }
.predikat-B { background: #eff6ff; color: #2563eb; }
.predikat-C { background: #fefce8; color: #ca8a04; }
.predikat-D { background: #fef2f2; color: #dc2626; }
</style>
@endpush

@section('content')

{{-- ACTION BAR (hidden on print) --}}
<div class="flex items-center gap-3 mb-5 rapor-actions">
    <a href="{{ route('academic.reports', ['class_id'=>$class->id, 'semester_id'=>$semester->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
    </a>
    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">
        <i data-lucide="printer" class="w-4 h-4"></i> Cetak Rapor
    </button>
</div>

<div class="rapor-wrapper bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    {{-- ═══════ HEADER ═══════ --}}
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
                <div class="text-xs text-white/70 uppercase tracking-wide mb-2">Kurikulum Merdeka</div>
                <h1 class="text-2xl font-bold">RAPOR</h1>
                <p class="text-sm text-white/80 mt-1">{{ $semester->name }} — T.A {{ $semester->academicYear->code ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════ IDENTITAS SISWA (selalu tampil setelah header) ═══════ --}}
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
                <span class="block text-xs text-slate-400 uppercase font-semibold mb-0.5">Kelas / Fase</span>
                <span class="font-semibold text-slate-800">{{ $class->code ?? '-' }} / {{ $phase }}</span>
            </div>
            <div>
                <span class="block text-xs text-slate-400 uppercase font-semibold mb-0.5">Tahun Ajaran</span>
                <span class="font-semibold text-slate-800">{{ $semester->academicYear->code ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ═══════ MODUL DINAMIS (sesuai urutan pengaturan) ═══════ --}}
    @foreach($raporModules as $mod)
        @if($mod['show'])
            @if($mod['key'] === 'nilai')
            {{-- A. NILAI INTAKURIKULER --}}
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-accent-100 flex items-center justify-center text-accent text-sm font-bold">A</span>
                    {{ $school->rapor_label_nilai ?? 'Nilai Intrakurikuler' }}
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-slate-200">
                                <th class="text-left py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-8">No</th>
                                <th class="text-left py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase">Mata Pelajaran</th>
                                <th class="text-center py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-24">Nilai Akhir</th>
                                <th class="text-center py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase w-24">Predikat</th>
                                <th class="text-left py-2.5 px-2 text-xs font-semibold text-slate-500 uppercase">Deskripsi Capaian Kompetensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php $no = 1; @endphp
                            @foreach($mapelList as $mapel)
                            @php
                                $hasDetail = $mapel['tp_count'] > 0;
                                $predikatClass = match($mapel['predikat']) {
                                    'A' => 'predikat-A', 'B' => 'predikat-B',
                                    'C' => 'predikat-C', default => 'predikat-D',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/30 group">
                                <td class="py-2.5 px-2 text-slate-400 align-top">{{ $no++ }}</td>
                                <td class="py-2.5 px-2 font-medium text-slate-700 align-top">
                                    {{ $mapel['nama'] }}
                                    @if($hasDetail)
                                    <span class="tp-toggle text-xs text-accent ml-1 cursor-pointer select-none" onclick="toggleTP(this, 'tp-{{ $mapel['cs_id'] }}')">
                                        <i data-lucide="chevron-down" class="w-3 h-3 inline chevron-icon"></i> Detail TP
                                    </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-2 text-center font-bold text-slate-800 align-top">{{ $mapel['nilai_akhir'] !== null ? number_format($mapel['nilai_akhir'], 0) : '-' }}</td>
                                <td class="py-2.5 px-2 text-center align-top">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $predikatClass }}">
                                        {{ $mapel['predikat'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-2 text-slate-600 text-xs leading-relaxed align-top">
                                    {{ $mapel['deskripsi'] ?: '—' }}
                                </td>
                            </tr>
                            @if($hasDetail)
                            <tr class="tp-table" id="tp-{{ $mapel['cs_id'] }}">
                                <td colspan="5" class="py-2 px-4 bg-slate-50/50 rounded-lg">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="border-b border-slate-200">
                                                    <th class="text-left py-1.5 px-2 text-slate-400 font-medium">Kode</th>
                                                    <th class="text-left py-1.5 px-2 text-slate-400 font-medium">Tujuan Pembelajaran</th>
                                                    <th class="text-center py-1.5 px-2 text-slate-400 font-medium w-16">Formatif</th>
                                                    <th class="text-center py-1.5 px-2 text-slate-400 font-medium w-16">Sumatif</th>
                                                    <th class="text-center py-1.5 px-2 text-slate-400 font-medium w-14">Rata²</th>
                                                    <th class="text-center py-1.5 px-2 text-slate-400 font-medium w-16">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($mapel['tps'] as $tp)
                                                @php
                                                    $tpAvg = $tp['rata_rata'];
                                                    $tpTuntas = $tpAvg !== null && $tpAvg >= ($mapel['kkm'] ?? 70);
                                                @endphp
                                                <tr>
                                                    <td class="py-1.5 px-2 text-slate-500 font-mono">{{ $tp['code'] }}</td>
                                                    <td class="py-1.5 px-2 text-slate-600">{{ \Illuminate\Support\Str::limit($tp['description'], 60) }}</td>
                                                    <td class="py-1.5 px-2 text-center font-semibold text-slate-600">{{ $tp['formatif'] ?? '-' }}</td>
                                                    <td class="py-1.5 px-2 text-center font-semibold text-slate-600">{{ $tp['sumatif'] ?? '-' }}</td>
                                                    <td class="py-1.5 px-2 text-center font-bold {{ $tpTuntas ? 'text-emerald-600' : 'text-red-500' }}">{{ $tpAvg !== null ? number_format($tpAvg, 0) : '-' }}</td>
                                                    <td class="py-1.5 px-2 text-center">
                                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $tpTuntas ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                                                            {{ $tpTuntas ? 'Tuntas' : 'Remedial' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center gap-4 mt-4 text-xs text-slate-400 flex-wrap">
                    <span class="font-semibold text-slate-500">Predikat:</span>
                    <span class="inline-flex items-center gap-1"><span class="w-5 h-5 rounded bg-emerald-50 text-emerald-600 text-[10px] font-bold inline-flex items-center justify-center">A</span> 90–100 (Sangat Baik)</span>
                    <span class="inline-flex items-center gap-1"><span class="w-5 h-5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold inline-flex items-center justify-center">B</span> 80–89 (Baik)</span>
                    <span class="inline-flex items-center gap-1"><span class="w-5 h-5 rounded bg-amber-50 text-amber-600 text-[10px] font-bold inline-flex items-center justify-center">C</span> 70–79 (Cukup)</span>
                    <span class="inline-flex items-center gap-1"><span class="w-5 h-5 rounded bg-red-50 text-red-600 text-[10px] font-bold inline-flex items-center justify-center">D</span> &lt;70 (Kurang)</span>
                </div>
            </div>
            @elseif($mod['key'] === 'p5')
            {{-- B. PROJEK P5 --}}
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 text-sm font-bold">B</span>
                    {{ $school->rapor_label_p5 ?? 'Projek Penguatan Profil Pelajar Pancasila (P5)' }}
                </h3>
                @forelse($p5Projects as $proj)
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-xs font-semibold">{{ $proj['tema'] }}</span>
                        <span class="font-semibold text-slate-700 text-sm">{{ $proj['judul'] }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-2">{{ $proj['deskripsi'] }}</p>
                    @if(!empty($proj['dimensi']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border border-slate-100 rounded-lg">
                            <thead><tr class="bg-slate-50">
                                <th class="text-left py-2 px-3 text-slate-500 font-semibold w-8">No</th>
                                <th class="text-left py-2 px-3 text-slate-500 font-semibold">Dimensi</th>
                                <th class="text-center py-2 px-3 text-slate-500 font-semibold w-20">Nilai</th>
                                <th class="text-center py-2 px-3 text-slate-500 font-semibold w-28">Kategori</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                @php $dimNo = 1; $dimensiLabels = ['Beriman, Bertakwa kepada Tuhan YME dan Berakhlak Mulia','Berkebinekaan Global','Gotong Royong','Mandiri','Bernalar Kritis','Kreatif']; @endphp
                                @foreach($proj['dimensi'] as $di => $dv)
                                @if($dv)
                                @php
                                    $katLabel = match($dv) { 'BB'=>'Belum Berkembang','MB'=>'Mulai Berkembang','BSH'=>'Berkembang Sesuai Harapan','SB'=>'Sangat Berkembang',default=>'—' };
                                    $katColor = match($dv) { 'BB'=>'bg-slate-100 text-slate-600','MB'=>'bg-sky-50 text-sky-600','BSH'=>'bg-emerald-50 text-emerald-600','SB'=>'bg-accent-100 text-accent-700',default=>'bg-slate-50 text-slate-400' };
                                @endphp
                                <tr>
                                    <td class="py-2 px-3 text-slate-400">{{ $dimNo++ }}</td>
                                    <td class="py-2 px-3 text-slate-600">{{ $dimensiLabels[$di] ?? 'Dimensi '.($di+1) }}</td>
                                    <td class="py-2 px-3 text-center font-bold text-slate-700">{{ $dv }}</td>
                                    <td class="py-2 px-3 text-center"><span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold {{ $katColor }}">{{ $katLabel }}</span></td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @if(!empty($proj['catatan']))
                    <p class="text-xs text-slate-500 mt-2 italic">"{{ $proj['catatan'] }}"</p>
                    @endif
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Belum ada projek P5 pada semester ini.</p>
                @endforelse
            </div>
            @elseif($mod['key'] === 'presensi')
            {{-- C. PRESENSI --}}
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-bold">C</span>
                    {{ $school->rapor_label_presensi ?? 'Kehadiran' }}
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
                <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                    <span>Total: <strong>{{ $attendanceRecap['total'] }}</strong> hari</span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span>Persentase Kehadiran: <strong class="text-emerald-600">{{ $attendanceRecap['persentase_hadir'] }}%</strong></span>
                </div>
            </div>
            @elseif($mod['key'] === 'catatan')
            {{-- D. CATATAN WALI KELAS --}}
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600 text-sm font-bold">D</span>
                    {{ $school->rapor_label_catatan ?? 'Catatan Wali Kelas' }}
                </h3>
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 min-h-[60px] text-sm text-slate-600 italic">
                    {{ $catatanWalikelas ?: 'Pertahankan dan tingkatkan prestasi belajar. Tetap semangat!' }}
                </div>
            </div>
            @elseif($mod['key'] === 'ttd')
            {{-- FOOTER / TANDA TANGAN --}}
            <div class="px-8 py-8">
                {{-- Baris 1: Orang Tua/Wali (kiri) | Wali Kelas + Tanggal (kanan) --}}
                <div class="flex justify-between mb-14">
                    {{-- Orang Tua/Wali --}}
                    @if($school->rapor_show_ttd_ortu ?? true)
                    <div class="text-center w-52">
                        {{-- Spacer agar sejajar dengan baris tanggal di sisi Wali Kelas --}}
                        <p class="text-xs text-white mb-1 select-none">—</p>
                        <p class="text-sm font-semibold text-slate-700 mb-12">Orang Tua / Wali</p>
                        <div class="mb-1">
                            <div class="border-b-2 border-slate-400 w-44 mx-auto h-8"></div>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 mt-1">{{ $orangTua->nama_lengkap ?? '______________________' }}</p>
                    </div>
                    @else
                    <div></div>
                    @endif

                    {{-- Wali Kelas + Tanggal --}}
                    @if($school->rapor_show_ttd_walikelas ?? true)
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
                    @endif
                </div>

                {{-- Baris 2: Kepala Sekolah (tengah) --}}
                @if($school->rapor_show_ttd_kepsek ?? true)
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
                @endif
            </div>
            @endif
        @endif
    @endforeach

</div>
@endsection

@push('scripts')
<script>
function toggleTP(btn, targetId) {
    const row = document.getElementById(targetId);
    const icon = btn.querySelector('.chevron-icon');
    if (row.classList.contains('open')) {
        row.classList.remove('open');
        row.style.display = 'none';
        if (icon) icon.classList.remove('rotate-180');
    } else {
        row.classList.add('open');
        row.style.display = 'table-row';
        if (icon) icon.classList.add('rotate-180');
    }
    try { lucide.createIcons(); } catch(e) {}
}
</script>
@endpush
