@extends('layouts.backend')

@section('title', 'Absensi Manual Siswa')
@section('page_title', 'Absensi Manual Siswa')

@section('content')
<div class="max-w-6xl mx-auto">

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-sm font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-red-500 text-sm font-medium flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-200">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-200"
                    onchange="this.form.submit()">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
                <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Tampilkan
            </button>
        </div>
    </form>

    @if($classId && $students->count())
    <form method="POST" action="{{ route('attendance.siswa.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        {{-- Quick Set All --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs text-slate-500 font-semibold mr-2">Set Semua:</span>
            @foreach(['hadir'=>'Hadir ✅','izin'=>'Izin 📝','sakit'=>'Sakit 🏥','alfa'=>'Alfa ❌','terlambat'=>'Terlambat ⏰'] as $val => $label)
            <button type="button" onclick="setAllStatus('{{ $val }}')"
                class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 hover:bg-slate-50 transition">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-slate-50">
                <h3 class="font-semibold text-slate-800">
                    {{ $students->first()->class->code ?? '' }} — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                </h3>
                <p class="text-xs text-slate-400">{{ $students->count() }} siswa</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase w-8">#</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Nama</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase w-24">NIS</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($students as $index => $student)
                        @php $att = $existingAttendance->get($student->id); @endphp
                        <tr class="hover:bg-slate-50/30">
                            <td class="py-2.5 px-4 text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-4 font-medium text-slate-700">{{ $student->nama_lengkap }}</td>
                            <td class="py-2.5 px-4 text-slate-500 font-mono text-xs">{{ $student->nis }}</td>
                            <td class="py-2.5 px-4 text-center">
                                <select name="status[{{ $student->id }}]" class="status-select px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 focus:ring-2 focus:ring-indigo-200"
                                    onchange="this.className='status-select px-3 py-1.5 rounded-lg text-xs font-medium border ' + getStatusClass(this.value)">
                                    <option value="hadir" {{ ($att->status ?? '') === 'hadir' ? 'selected' : '' }}>✅ Hadir</option>
                                    <option value="izin" {{ ($att->status ?? '') === 'izin' ? 'selected' : '' }}>📝 Izin</option>
                                    <option value="sakit" {{ ($att->status ?? '') === 'sakit' ? 'selected' : '' }}>🏥 Sakit</option>
                                    <option value="alfa" {{ ($att->status ?? '') === 'alfa' ? 'selected' : '' }}>❌ Alfa</option>
                                    <option value="terlambat" {{ ($att->status ?? '') === 'terlambat' ? 'selected' : '' }}>⏰ Terlambat</option>
                                </select>
                            </td>
                            <td class="py-2.5 px-4">
                                <input type="text" name="keterangan[{{ $student->id }}]" value="{{ $att->keterangan ?? '' }}"
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-200"
                                    placeholder="Opsional">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end mt-5">
            <button type="submit"
                class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl text-sm hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Presensi
            </button>
        </div>
    </form>
    @elseif($classId)
    <div class="text-center py-12 bg-white rounded-2xl border border-slate-100">
        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
        <p class="text-slate-400">Pilih kelas untuk memulai input presensi.</p>
    </div>
    @endif
</div>

<script>
function setAllStatus(status) {
    document.querySelectorAll('.status-select').forEach(sel => {
        sel.value = status;
        sel.className = 'status-select px-3 py-1.5 rounded-lg text-xs font-medium border ' + getStatusClass(status);
    });
}
function getStatusClass(status) {
    return {
        'hadir': 'border-emerald-300 bg-emerald-50 text-emerald-700',
        'izin': 'border-amber-300 bg-amber-50 text-amber-700',
        'sakit': 'border-orange-300 bg-orange-50 text-orange-700',
        'alfa': 'border-red-300 bg-red-50 text-red-700',
        'terlambat': 'border-yellow-300 bg-yellow-50 text-yellow-700',
    }[status] || 'border-slate-200';
}
lucide.createIcons();
</script>
@endsection
