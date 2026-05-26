@extends('layouts.backend')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    {{-- Total Siswa --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-indigo-100 hover:shadow-sm transition-all duration-200 group">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Siswa</div>
                <div class="text-3xl font-bold text-slate-800 tracking-tight">{{ number_format($totalSiswa, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-1">Aktif</div>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-indigo-50 group-hover:bg-indigo-100 transition">
                <i data-lucide="graduation-cap" class="w-5 h-5 text-indigo-600"></i>
            </div>
        </div>
    </div>

    {{-- Guru & Staff --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-emerald-100 hover:shadow-sm transition-all duration-200 group">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Guru &amp; Staff</div>
                <div class="text-3xl font-bold text-slate-800 tracking-tight">{{ number_format($totalGuruStaff, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-1">Total</div>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-emerald-50 group-hover:bg-emerald-100 transition">
                <i data-lucide="briefcase" class="w-5 h-5 text-emerald-600"></i>
            </div>
        </div>
    </div>

    {{-- Rombel --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-violet-100 hover:shadow-sm transition-all duration-200 group">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Rombel Aktif</div>
                <div class="text-3xl font-bold text-slate-800 tracking-tight">{{ number_format($totalRombel, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-1">Kelas</div>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-violet-50 group-hover:bg-violet-100 transition">
                <i data-lucide="school" class="w-5 h-5 text-violet-600"></i>
            </div>
        </div>
    </div>

    {{-- Pemasukan --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-amber-100 hover:shadow-sm transition-all duration-200 group">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pemasukan</div>
                <div class="text-3xl font-bold text-slate-800 tracking-tight">Rp{{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-400 mt-1">Bulan Ini</div>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-amber-50 group-hover:bg-amber-100 transition">
                <i data-lucide="wallet" class="w-5 h-5 text-amber-600"></i>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Stats --}}
@if(!empty($attendance_bulan_ini) && ($attendance_bulan_ini['total'] ?? 0) > 0)
<div class="mb-8">
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Presensi Bulan Ini</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ $attendance_bulan_ini['hadir'] ?? 0 }}</div>
            <div class="text-xs text-slate-400 mt-1">Hadir</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $attendance_bulan_ini['terlambat'] ?? 0 }}</div>
            <div class="text-xs text-slate-400 mt-1">Terlambat</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $attendance_bulan_ini['izin'] ?? 0 }}</div>
            <div class="text-xs text-slate-400 mt-1">Izin</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $attendance_bulan_ini['sakit'] ?? 0 }}</div>
            <div class="text-xs text-slate-400 mt-1">Sakit</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $attendance_bulan_ini['alfa'] ?? 0 }}</div>
            <div class="text-xs text-slate-400 mt-1">Alfa</div>
        </div>
    </div>
    <div class="mt-3 bg-white rounded-2xl p-4 border border-slate-100 flex items-center gap-4">
        <span class="text-sm text-slate-500">Tingkat Kehadiran:</span>
        <div class="flex-1 bg-slate-100 rounded-full h-3 overflow-hidden">
            <div class="h-full rounded-full {{ ($attendance_bulan_ini['persentase_kehadiran'] ?? 0) >= 90 ? 'bg-emerald-500' : (($attendance_bulan_ini['persentase_kehadiran'] ?? 0) >= 75 ? 'bg-amber-500' : 'bg-red-500') }}" style="width:{{ $attendance_bulan_ini['persentase_kehadiran'] ?? 0 }}%"></div>
        </div>
        <span class="text-sm font-bold {{ ($attendance_bulan_ini['persentase_kehadiran'] ?? 0) >= 90 ? 'text-emerald-600' : (($attendance_bulan_ini['persentase_kehadiran'] ?? 0) >= 75 ? 'text-amber-600' : 'text-red-600') }}">{{ $attendance_bulan_ini['persentase_kehadiran'] ?? 0 }}%</span>
    </div>
</div>
@endif

{{-- Per-Kelas Attendance (kepsek/admin) --}}
@if(!empty($attendance_per_kelas) && $attendance_per_kelas->isNotEmpty())
<div class="mb-8">
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Tingkat Kehadiran Per Kelas — Bulan Ini</h3>
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Kelas</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Tkt</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Siswa</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">H</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">T</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">I</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">S</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">A</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @foreach($attendance_per_kelas as $k)
                @php
                    $pctColor = $k->persentase_kehadiran >= 90 ? 'text-emerald-600' : ($k->persentase_kehadiran >= 75 ? 'text-amber-600' : 'text-red-600');
                    $barColor = $k->persentase_kehadiran >= 90 ? 'bg-emerald-500' : ($k->persentase_kehadiran >= 75 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $k->code }}</td>
                    <td class="px-4 py-3 text-center text-xs text-slate-500">{{ $k->tingkat }}</td>
                    <td class="px-4 py-3 text-center text-xs text-slate-500">{{ $k->total_students }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-emerald-600">{{ $k->hadir }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-yellow-600">{{ $k->terlambat }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-amber-600">{{ $k->izin }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-orange-600">{{ $k->sakit }}</td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-red-600">{{ $k->alfa }}</td>
                    <td class="px-4 py-3 text-center text-xs">
                        <div class="flex items-center gap-2 justify-end">
                            <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full {{ $barColor }}" style="width:{{ $k->persentase_kehadiran }}%"></div>
                            </div>
                            <span class="text-xs font-bold {{ $pctColor }} w-10 text-right">{{ $k->persentase_kehadiran }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Staff Attendance Bulan Ini (kepsek/admin) --}}
@if(!empty($staff_attendance_bulan_ini) && ($staff_attendance_bulan_ini['total'] ?? 0) > 0)
<div class="mb-8">
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Absensi Pegawai — Bulan Ini</h3>
    <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <div class="flex flex-wrap gap-6 items-center">
            <div class="text-center">
                <div class="text-2xl font-bold {{ ($staff_attendance_bulan_ini['persentase_hadir'] ?? 0) >= 90 ? 'text-emerald-600' : ((($staff_attendance_bulan_ini['persentase_hadir'] ?? 0) >= 75) ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $staff_attendance_bulan_ini['persentase_hadir'] ?? 0 }}%
                </div>
                <div class="text-xs text-slate-400">Tingkat Kehadiran</div>
            </div>
            <div class="flex-1">
                <div class="flex gap-3 flex-wrap text-sm">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Hadir: <strong>{{ $staff_attendance_bulan_ini['hadir'] ?? 0 }}</strong></span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Terlambat: <strong>{{ $staff_attendance_bulan_ini['terlambat'] ?? 0 }}</strong></span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Izin: <strong>{{ $staff_attendance_bulan_ini['izin'] ?? 0 }}</strong></span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-orange-400"></span> Sakit: <strong>{{ $staff_attendance_bulan_ini['sakit'] ?? 0 }}</strong></span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Alfa: <strong>{{ $staff_attendance_bulan_ini['alfa'] ?? 0 }}</strong></span>
                </div>
                <div class="mt-2 bg-slate-100 rounded-full h-2 overflow-hidden w-full max-w-md">
                    @php $spct = $staff_attendance_bulan_ini['persentase_hadir'] ?? 0; @endphp
                    <div class="h-full rounded-full {{ $spct >= 90 ? 'bg-emerald-500' : ($spct >= 75 ? 'bg-amber-500' : 'bg-red-500') }}" style="width:{{ $spct }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Per-Jabatan Staff Attendance (kepsek/admin) --}}
@if(!empty($staff_attendance_by_jabatan))
<div class="mb-8">
    <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Kehadiran Pegawai Per Jabatan — Bulan Ini</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($staff_attendance_by_jabatan as $row)
        @php $jpct = $row['persentase_kehadiran']; @endphp
        <div class="bg-white rounded-xl border border-slate-100 p-3.5">
            <div class="flex justify-between items-start mb-2">
                <span class="text-sm font-medium text-slate-700">{{ $row['label'] }}</span>
                <span class="text-xs font-bold {{ $jpct >= 90 ? 'text-emerald-600' : ($jpct >= 75 ? 'text-amber-600' : 'text-red-600') }}">{{ $jpct }}%</span>
            </div>
            <div class="flex gap-3 text-xs">
                <span class="text-emerald-600">H:{{ $row['hadir'] }}</span>
                <span class="text-yellow-600">T:{{ $row['terlambat'] }}</span>
                <span class="text-blue-600">I:{{ $row['izin'] }}</span>
                <span class="text-orange-600">S:{{ $row['sakit'] }}</span>
                <span class="text-red-600">A:{{ $row['alfa'] }}</span>
            </div>
            <div class="mt-2 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full {{ $jpct >= 90 ? 'bg-emerald-500' : ($jpct >= 75 ? 'bg-amber-500' : 'bg-red-500') }}" style="width:{{ $jpct }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-2 text-right">
        <a href="{{ route('staff.attendance.recap') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Lihat rekap lengkap →</a>
    </div>
</div>
@endif

{{-- Welcome & Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
    {{-- Welcome --}}
    <div class="lg:col-span-3 bg-white rounded-2xl p-6 border border-slate-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center">
                <i data-lucide="hand" class="w-4 h-4 text-indigo-600"></i>
            </div>
            <h3 class="font-semibold text-slate-800">Selamat Datang di SIAKAD</h3>
        </div>
        <p class="text-sm text-slate-500 leading-relaxed mb-4">
            Sistem Informasi Akademik <strong>{{ config('app.siakad_school_name', 'Sekolah') }}</strong>.
            Kelola data master, input nilai, atur ujian, dan pantau pembayaran melalui menu di sidebar.
        </p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ url('/backend/master/students') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i> Data Siswa
            </a>
            <a href="{{ url('/backend/academic/grades') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> Input Nilai
            </a>
            <a href="{{ url('/backend/finance/payments') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Pembayaran
            </a>
        </div>
    </div>

    {{-- Activity --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-lg bg-sky-100 flex items-center justify-center">
                <i data-lucide="activity" class="w-4 h-4 text-sky-600"></i>
            </div>
            <h3 class="font-semibold text-slate-800">Aktivitas</h3>
        </div>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                <span class="text-sm text-slate-500">Sistem berjalan normal</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                <span class="text-sm text-slate-500">Login: {{ auth()->user()->last_login_at ?? '-' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                <span class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
