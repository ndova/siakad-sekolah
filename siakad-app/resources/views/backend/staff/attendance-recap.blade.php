@extends('layouts.backend')
@section('title', 'Rekap Absensi Pegawai — SIAKAD')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Rekap Absensi Pegawai</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Bulan {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('staff.index') }}" class="btn-secondary text-sm px-4 py-2 rounded-lg">Daftar Pegawai</a>
            <a href="{{ route('staff.attendance.grid') }}" class="btn-secondary text-sm px-4 py-2 rounded-lg">Grid Absensi</a>
            <a href="{{ route('staff.attendance.export', ['year'=>$year,'month'=>$month]) }}" class="btn-primary text-sm px-4 py-2 rounded-lg flex items-center gap-1">
                📥 Ekspor CSV
            </a>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3 mb-6">
        <form class="flex flex-wrap gap-3 items-center">
            @foreach(range(1,12) as $m)
            <a href="?month={{ $m }}&year={{ $year }}&jabatan={{ $jabatan }}"
               class="text-xs px-2.5 py-1 rounded-full {{ $month == $m ? 'btn-accent text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
            </a>
            @endforeach
            <span class="text-slate-300">|</span>
            <select name="year" onchange="this.form.submit()" class="form-select text-xs w-20">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="jabatan" onchange="this.form.submit()" class="form-select text-xs w-36">
                <option value="">Semua Jabatan</option>
                @foreach($jabatanList as $j => $label)
                <option value="{{ $j }}" {{ $jabatan === $j ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Per-Jabatan Summary --}}
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Ringkasan Per Jabatan</h3>
    @if(!empty($byJabatan))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($byJabatan as $row)
        <div class="bg-white rounded-xl border border-slate-100 p-4 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-semibold text-slate-800">{{ $row['label'] }}</h4>
                    <p class="text-xs text-slate-400">{{ $row['total_staff'] }} pegawai</p>
                </div>
                @php
                    $pct = $row['persentase_kehadiran'];
                    $pctColor = $pct >= 90 ? 'text-emerald-600' : ($pct >= 75 ? 'text-amber-600' : 'text-red-600');
                @endphp
                <span class="text-lg font-bold {{ $pctColor }}">{{ $pct }}%</span>
            </div>
            <div class="grid grid-cols-5 gap-2 text-center text-xs">
                <div><span class="text-emerald-600 font-bold block">{{ $row['hadir'] }}</span><span class="text-slate-400">Hadir</span></div>
                <div><span class="text-yellow-600 font-bold block">{{ $row['terlambat'] }}</span><span class="text-slate-400">T</span></div>
                <div><span class="text-blue-600 font-bold block">{{ $row['izin'] }}</span><span class="text-slate-400">Izin</span></div>
                <div><span class="text-orange-600 font-bold block">{{ $row['sakit'] }}</span><span class="text-slate-400">Sakit</span></div>
                <div><span class="text-red-600 font-bold block">{{ $row['alfa'] }}</span><span class="text-slate-400">Alfa</span></div>
            </div>
            {{-- Progress bar --}}
            <div class="mt-3 bg-slate-100 rounded-full h-2 overflow-hidden">
                @php $barColor = $pct >= 90 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-red-500'); @endphp
                <div class="h-full rounded-full {{ $barColor }} transition-all" style="width:{{ $pct }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Per-Staff Detail Table --}}
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Rekap Per Pegawai</h3>
    @if($staffList->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden table-responsive">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jabatan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">H</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">T</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">I</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">S</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">A</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">% Hadir</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @foreach($staffList as $i => $row)
                @php
                    $pct = $row['persentase_hadir'];
                    $barColor = $pct >= 90 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-red-500');
                    $pctColor = $pct >= 90 ? 'text-emerald-600' : ($pct >= 75 ? 'text-amber-600' : 'text-red-600');
                    $alfa = $row['alfa'];
                @endphp
                <tr class="hover:bg-slate-50/50 {{ $alfa >= 3 ? 'bg-red-50/30' : '' }}">
                    <td class="px-4 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['staff']->nama_lengkap }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $row['staff']->nip ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">
                        <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px]">
                            {{ \App\Models\Staff::jabatanLabel($row['staff']->jabatan) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-emerald-600">{{ $row['hadir'] }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-yellow-600">{{ $row['terlambat'] }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-blue-600">{{ $row['izin'] }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-orange-600">{{ $row['sakit'] }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-red-600">{{ $row['alfa'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center gap-2 justify-end">
                            <div class="w-14 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full {{ $barColor }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-xs font-bold {{ $pctColor }} min-w-[3rem] text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($alfa >= 5)
                        <span class="text-xs text-red-600 font-bold">⚠️ Alfa Tinggi</span>
                        @elseif($alfa >= 3)
                        <span class="text-xs text-amber-600">Perhatian</span>
                        @else
                        <span class="text-xs text-emerald-600">Baik</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $staffList->links() }}</div>
    </div>
    @else
    <div class="bg-white rounded-xl border border-slate-100 p-12 text-center text-slate-400">
        Belum ada data absensi untuk periode ini.
    </div>
    @endif
</div>
@endsection
