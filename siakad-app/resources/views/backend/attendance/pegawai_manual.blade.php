@extends('layouts.backend')

@section('title', 'Absensi Manual Pegawai')
@section('page_title', 'Absensi Manual Pegawai')

@section('content')
<div class="max-w-6xl mx-auto">

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-sm font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Jabatan</label>
                <select name="jabatan" onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-200">
                    <option value="">Semua Jabatan</option>
                    @foreach($jabatans as $j)
                    <option value="{{ $j }}" {{ $jabatan == $j ? 'selected' : '' }}>{{ \App\Models\Staff::jabatanLabel($j) }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('attendance.pegawai.form') }}" class="px-4 py-2.5 text-sm text-slate-500 hover:text-slate-700">
                Reset Filter
            </a>
        </div>
    </form>

    @if($staffList->count())
    <form method="POST" action="{{ route('attendance.pegawai.store') }}">
        @csrf
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

        {{-- Set Jam Default --}}
        <div class="flex flex-wrap items-center gap-3 mb-4 p-3 bg-slate-50 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-semibold">Set Jam:</span>
            <input type="time" id="defaultJamMasuk" value="07:00"
                class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs">
            <button type="button" onclick="setAllTime('check_in_time', document.getElementById('defaultJamMasuk').value)"
                class="px-3 py-1 rounded-lg text-xs bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition">Jam Masuk</button>
            <input type="time" id="defaultJamPulang" value="15:00"
                class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs">
            <button type="button" onclick="setAllTime('check_out_time', document.getElementById('defaultJamPulang').value)"
                class="px-3 py-1 rounded-lg text-xs bg-purple-100 text-purple-600 hover:bg-purple-200 transition">Jam Pulang</button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-slate-50">
                <h3 class="font-semibold text-slate-800">
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                </h3>
                <p class="text-xs text-slate-400">{{ $staffList->count() }} pegawai</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase w-8">#</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Nama</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Jabatan</th>
                            <th class="text-center py-3 px-2 text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="text-center py-3 px-2 text-xs font-semibold text-slate-500 uppercase w-24">Jam Masuk</th>
                            <th class="text-center py-3 px-2 text-xs font-semibold text-slate-500 uppercase w-24">Jam Pulang</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($staffList as $index => $staff)
                        @php $att = $existingAttendance->get($staff->id); @endphp
                        <tr class="hover:bg-slate-50/30">
                            <td class="py-2.5 px-4 text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-4 font-medium text-slate-700">
                                {{ $staff->nama_lengkap }}
                                <div class="text-xs text-slate-400">{{ $staff->nip }}</div>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs">
                                    {{ \App\Models\Staff::jabatanLabel($staff->jabatan) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-2 text-center">
                                <select name="status[{{ $staff->id }}]" class="status-select px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200"
                                    onchange="this.className='status-select px-3 py-1.5 rounded-lg text-xs font-medium border ' + getStatusClass(this.value)">
                                    <option value="hadir" {{ ($att->status ?? '') === 'hadir' ? 'selected' : '' }}>✅ Hadir</option>
                                    <option value="izin" {{ ($att->status ?? '') === 'izin' ? 'selected' : '' }}>📝 Izin</option>
                                    <option value="sakit" {{ ($att->status ?? '') === 'sakit' ? 'selected' : '' }}>🏥 Sakit</option>
                                    <option value="alfa" {{ ($att->status ?? '') === 'alfa' ? 'selected' : '' }}>❌ Alfa</option>
                                    <option value="terlambat" {{ ($att->status ?? '') === 'terlambat' ? 'selected' : '' }}>⏰ Terlambat</option>
                                </select>
                            </td>
                            <td class="py-2.5 px-2">
                                <input type="time" name="check_in_time[{{ $staff->id }}]"
                                    value="{{ $att->check_in_time ?? '' }}"
                                    class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-center focus:ring-2 focus:ring-indigo-200"
                                    style="max-width:110px;">
                            </td>
                            <td class="py-2.5 px-2">
                                <input type="time" name="check_out_time[{{ $staff->id }}]"
                                    value="{{ $att->check_out_time ?? '' }}"
                                    class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-center focus:ring-2 focus:ring-indigo-200"
                                    style="max-width:110px;">
                            </td>
                            <td class="py-2.5 px-4">
                                <input type="text" name="keterangan[{{ $staff->id }}]"
                                    value="{{ $att->keterangan ?? '' }}"
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
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Presensi Pegawai
            </button>
        </div>
    </form>
    @else
    <div class="text-center py-12 bg-white rounded-2xl border border-slate-100">
        <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
        <p class="text-slate-400">Tidak ada pegawai ditemukan.</p>
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
function setAllTime(fieldName, value) {
    document.querySelectorAll('input[name^="' + fieldName + '"]').forEach(inp => {
        inp.value = value;
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
