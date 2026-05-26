@extends('layouts.backend')

@section('title', 'Presensi')
@section('page_title', 'Presensi Harian')

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border-accent-100 text-accent text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

    {{-- Sidebar: Jadwal Mengajar (Guru) --}}
    @if($myClassSubjects->isNotEmpty())
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
            <div class="px-5 py-4 border-b bg-accent-50">
                <h4 class="font-semibold text-sm text-accent flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4"></i> Jadwal Mengajar</h4>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($myClassSubjects as $cs)
                <a href="?class_subject_id={{ $cs->id }}&tanggal={{ $tanggal }}"
                   class="block px-5 py-3 hover:bg-slate-50 transition {{ $classSubjectId == $cs->id ? 'bg-accent-50 border-l-2 border-accent' : '' }}">
                    <div class="text-sm font-medium text-slate-800">{{ $cs->subject->name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $cs->schoolClass->code }} · {{ $cs->schoolClass->tingkat }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="lg:col-span-3">
    @else
    <div class="lg:col-span-4">
    @endif

        {{-- Filter --}}
        <form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label>
                    <select name="class_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $k)<option value="{{ $k->id }}" {{ $classId==$k->id?'selected':'' }}>{{ $k->code }} - {{ $k->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                </div>
                @if($classSubjectId)<input type="hidden" name="class_subject_id" value="{{ $classSubjectId }}">@endif
                <a href="{{ route('academic.attendance.recap') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-sm text-slate-600 hover:bg-slate-200 flex items-center gap-1.5"><i data-lucide="bar-chart-3" class="w-4 h-4"></i> Lihat Rekap</a>
            </div>
        </form>

        @if(($classSubject || $classId) && $students->count())
        <form method="POST" action="{{ route('academic.attendance.bulk') }}" class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        @csrf
        @if($classSubjectId)<input type="hidden" name="class_subject_id" value="{{ $classSubjectId }}">@endif
        <input type="hidden" name="class_id" value="{{ $classSubject ? $classSubject->class_id : $classId }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="p-5 border-b flex flex-wrap justify-between items-center gap-3">
            <div>
                <h3 class="font-semibold text-slate-800">Presensi — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</h3>
                @if($classSubject)
                <p class="text-xs text-slate-400 mt-0.5">{{ $classSubject->subject->name }} · {{ $classSubject->schoolClass->code }}</p>
                @endif
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="button" onclick="setAll('hadir')" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-xs font-medium text-emerald-600 hover:bg-emerald-100">✅ Hadir Semua</button>
                <button type="button" onclick="setAll('terlambat')" class="px-3 py-1.5 rounded-lg bg-yellow-50 text-xs font-medium text-yellow-600 hover:bg-yellow-100">⏰ Terlambat</button>
                <button type="button" onclick="setAll('izin')" class="px-3 py-1.5 rounded-lg bg-amber-50 text-xs font-medium text-amber-600 hover:bg-amber-100">📝 Izin</button>
                <button type="button" onclick="setAll('sakit')" class="px-3 py-1.5 rounded-lg bg-orange-50 text-xs font-medium text-orange-600 hover:bg-orange-100">🏥 Sakit</button>
                <button type="button" onclick="setAll('alfa')" class="px-3 py-1.5 rounded-lg bg-red-50 text-xs font-medium text-red-600 hover:bg-red-100">❌ Alfa</button>
            </div>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-sm">
        <thead><tr class="bg-slate-50">
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Keterangan</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-50">
        @foreach($students as $student)
        @php $att = $attendances[$student->id] ?? null; @endphp
        <tr class="hover:bg-slate-50/50">
            <td class="px-4 py-3 font-mono text-sm">{{ $student->nis }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">{{ $student->nama_lengkap }}</td>
            <td class="px-4 py-3 text-center">
                <select name="status[{{ $student->id }}]" class="att-status px-3 py-1.5 rounded-lg border text-xs focus:ring-2 focus:ring-accent-200
                    {{ ($att->status ?? '') === 'hadir' ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : '' }}
                    {{ ($att->status ?? '') === 'terlambat' ? 'border-yellow-300 bg-yellow-50 text-yellow-700' : '' }}
                    {{ ($att->status ?? '') === 'izin' ? 'border-amber-300 bg-amber-50 text-amber-700' : '' }}
                    {{ ($att->status ?? '') === 'sakit' ? 'border-orange-300 bg-orange-50 text-orange-700' : '' }}
                    {{ ($att->status ?? '') === 'alfa' || ($att->status ?? '') === 'tidak_hadir' ? 'border-red-300 bg-red-50 text-red-700' : '' }}
                    border-slate-200">
                    <option value="">-- Pilih --</option>
                    <option value="hadir" {{ $att && $att->status=='hadir' ? 'selected' : '' }}>✅ Hadir</option>
                    <option value="terlambat" {{ $att && $att->status=='terlambat' ? 'selected' : '' }}>⏰ Terlambat</option>
                    <option value="izin" {{ $att && $att->status=='izin' ? 'selected' : '' }}>📝 Izin</option>
                    <option value="sakit" {{ $att && $att->status=='sakit' ? 'selected' : '' }}>🏥 Sakit</option>
                    <option value="alfa" {{ $att && $att->status=='alfa' ? 'selected' : '' }} {{ $att && $att->status=='tidak_hadir' ? 'selected' : '' }}>❌ Alfa</option>
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="text" name="keterangan[{{ $student->id }}]" value="{{ $att->keterangan ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-accent-200" placeholder="Keterangan...">
            </td>
        </tr>
        @endforeach
        </tbody></table>
        </div>

        @php
        $filledCount = $attendances->count();
        $totalStudents = $students->count();
        @endphp
        <div class="p-5 border-t flex flex-wrap justify-between items-center gap-3">
            <div class="text-xs text-slate-400">
                Terisi: <span class="font-semibold text-slate-600">{{ $filledCount }}/{{ $totalStudents }}</span>
                @if($filledCount > 0)
                · Hadir: {{ $attendances->where('status','hadir')->count() }}
                · Terlambat: {{ $attendances->where('status','terlambat')->count() }}
                · Izin: {{ $attendances->where('status','izin')->count() }}
                · Sakit: {{ $attendances->where('status','sakit')->count() }}
                · Alfa: {{ $attendances->where('status','alfa')->count() + $attendances->where('status','tidak_hadir')->count() }}
                @endif
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl btn-accent text-white text-sm font-medium flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Simpan Presensi</button>
        </div>
        </form>
        @else
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
            <i data-lucide="calendar-check" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
            <p class="text-slate-400">
                @if($myClassSubjects->isNotEmpty())
                    Pilih jadwal mengajar di sidebar atau pilih kelas dan tanggal untuk mengisi presensi.
                @else
                    Pilih kelas dan tanggal untuk mengisi presensi.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function setAll(status){document.querySelectorAll('.att-status').forEach(s=>s.value=status)}
</script>
@endpush
