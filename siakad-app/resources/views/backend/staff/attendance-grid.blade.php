@extends('layouts.backend')
@section('title', 'Grid Absensi Pegawai — SIAKAD')

@push('styles')
<style>
.modal-content {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    width: 100%;
    position: relative;
    z-index: 51;
}
</style>
@endpush

@section('content')
<div class="max-w-full mx-auto">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Grid Absensi Pegawai</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Bulan {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }} —
                {{ $staffList->count() }} pegawai
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('staff.index') }}" class="btn-secondary text-sm px-4 py-2 rounded-lg">Daftar Pegawai</a>
            <a href="{{ route('staff.attendance.recap') }}" class="btn-secondary text-sm px-4 py-2 rounded-lg">Rekap</a>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 text-xs">
        <span class="flex items-center gap-1"><span class="w-3.5 h-3.5 rounded bg-emerald-500"></span> Hadir</span>
        <span class="flex items-center gap-1"><span class="w-3.5 h-3.5 rounded bg-yellow-400"></span> Terlambat</span>
        <span class="flex items-center gap-1"><span class="w-3.5 h-3.5 rounded bg-blue-400"></span> Izin</span>
        <span class="flex items-center gap-1"><span class="w-3.5 h-3.5 rounded bg-orange-400"></span> Sakit</span>
        <span class="flex items-center gap-1"><span class="w-3.5 h-3.5 rounded bg-red-500"></span> Alfa</span>
        <span class="flex items-center gap-1"><span class="w-3.5 h-3.5 rounded bg-slate-200"></span> Belum</span>
    </div>

    {{-- Summary bar --}}
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3 mb-3 flex flex-wrap gap-4 text-sm">
        @if(!empty($statusSummary))
        <span class="text-emerald-600 font-semibold">✅ Hadir: {{ $statusSummary['hadir'] ?? 0 }}</span>
        <span class="text-yellow-600 font-semibold">⏰ Terlambat: {{ $statusSummary['terlambat'] ?? 0 }}</span>
        <span class="text-blue-600 font-semibold">📝 Izin: {{ $statusSummary['izin'] ?? 0 }}</span>
        <span class="text-orange-600 font-semibold">🏥 Sakit: {{ $statusSummary['sakit'] ?? 0 }}</span>
        <span class="text-red-600 font-semibold">❌ Alfa: {{ $statusSummary['alfa'] ?? 0 }}</span>
        <span class="text-slate-500">Total: {{ $statusSummary['total'] ?? 0 }}</span>
        <span class="text-indigo-600 font-bold">
            Tingkat Kehadiran: {{ $statusSummary['persentase_hadir'] ?? 0 }}%
        </span>
        @endif
    </div>

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3 mb-4">
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

    {{-- Bulk action untuk satu tanggal --}}
    <form id="bulkForm" method="POST" action="{{ route('staff.attendance.bulk') }}" class="mb-4">
        @csrf
        <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl px-4 py-3">
            <label class="text-xs font-medium text-slate-600">Isi massal tanggal:</label>
            <input type="date" name="tanggal" value="{{ collect($dates)->last() }}" class="form-input text-xs w-36">
            <select id="bulkStatus" class="form-select text-xs w-24">
                <option value="hadir">Hadir</option>
                <option value="terlambat">Terlambat</option>
                <option value="izin">Izin</option>
                <option value="sakit">Sakit</option>
                <option value="alfa">Alfa</option>
            </select>
            <button type="button" onclick="bulkFill()" class="btn-primary text-xs px-3 py-1 rounded-lg">Isi Semua</button>
        </div>
    </form>

    {{-- Grid Table — scrollable horizontal --}}
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden table-responsive">
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 sticky top-0 z-20">
                        <th class="sticky left-0 bg-slate-50 px-3 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase min-w-[160px] z-30">
                            Pegawai / Jabatan
                        </th>
                        @foreach($dates as $date)
                        <th class="px-1 py-2.5 text-center text-[10px] font-semibold w-9 {{ \Carbon\Carbon::parse($date)->isWeekend() ? 'text-red-400' : 'text-slate-500' }}">
                            <div>{{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}</div>
                            <div>{{ \Carbon\Carbon::parse($date)->format('d') }}</div>
                        </th>
                        @endforeach
                        <th class="px-2 py-2.5 text-center text-[10px] font-semibold text-slate-500 uppercase w-12">H</th>
                        <th class="px-2 py-2.5 text-center text-[10px] font-semibold text-slate-500 uppercase w-12">T</th>
                        <th class="px-2 py-2.5 text-center text-[10px] font-semibold text-slate-500 uppercase w-12">I</th>
                        <th class="px-2 py-2.5 text-center text-[10px] font-semibold text-slate-500 uppercase w-12">S</th>
                        <th class="px-2 py-2.5 text-center text-[10px] font-semibold text-slate-500 uppercase w-12">A</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @php $lastJabatan = ''; @endphp
                @foreach($staffList as $staff)
                    @if($lastJabatan !== $staff->jabatan)
                    @php $lastJabatan = $staff->jabatan; @endphp
                    <tr class="bg-slate-50/70">
                        <td colspan="{{ $days + 6 }}" class="px-3 py-1.5 text-[10px] font-bold text-slate-500 uppercase">
                            {{ \App\Models\Staff::jabatanLabel($staff->jabatan) }}
                        </td>
                    </tr>
                    @endif
                    @php
                        $hadirCount = 0; $terlambatCount = 0; $izinCount = 0; $sakitCount = 0; $alfaCount = 0;
                    @endphp
                    <tr class="hover:bg-slate-50/30">
                        <td class="sticky left-0 bg-white px-3 py-2 border-r border-slate-100 z-[1]">
                            <div class="font-medium text-slate-800 truncate max-w-[150px]">{{ $staff->nama_lengkap }}</div>
                            <div class="text-[10px] text-slate-400">{{ $staff->nip }}</div>
                        </td>
                        @foreach($dates as $date)
                        @php
                            $att = $attendances->get($staff->id . '|' . $date);
                            if ($att) {
                                match ($att->status) {
                                    'hadir' => $hadirCount++,
                                    'terlambat' => $terlambatCount++,
                                    'izin' => $izinCount++,
                                    'sakit' => $sakitCount++,
                                    'alfa' => $alfaCount++,
                                    default => null,
                                };
                            }
                            $cellColor = match($att?->status) {
                                'hadir' => 'bg-emerald-500',
                                'terlambat' => 'bg-yellow-400',
                                'izin' => 'bg-blue-400',
                                'sakit' => 'bg-orange-400',
                                'alfa' => 'bg-red-500',
                                default => 'bg-slate-100 hover:bg-slate-200',
                            };
                            $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                        @endphp
                        <td class="px-0 py-1 text-center {{ $isWeekend ? 'bg-slate-50/30' : '' }}">
                            <button type="button"
                                onclick="setAttendance('{{ $staff->id }}', '{{ $date }}', '{{ $att?->status }}')"
                                title="{{ $att ? \App\Models\StaffAttendance::statusLabel($att->status) . ($att->keterangan ? ': ' . $att->keterangan : '') : 'Belum diisi — klik untuk isi' }}"
                                class="w-7 h-7 rounded-full {{ $cellColor }} transition-transform hover:scale-110 inline-flex items-center justify-center text-white text-[9px] font-bold">
                                {{ $att ? strtoupper(substr($att->status, 0, 1)) : '' }}
                            </button>
                        </td>
                        @endforeach
                        <td class="px-1 text-center text-[10px] font-semibold text-emerald-600">{{ $hadirCount }}</td>
                        <td class="px-1 text-center text-[10px] font-semibold text-yellow-600">{{ $terlambatCount }}</td>
                        <td class="px-1 text-center text-[10px] font-semibold text-blue-600">{{ $izinCount }}</td>
                        <td class="px-1 text-center text-[10px] font-semibold text-orange-600">{{ $sakitCount }}</td>
                        <td class="px-1 text-center text-[10px] font-semibold text-red-600">{{ $alfaCount }}</td>
                    </tr>
                @endforeach
                @if($staffList->isEmpty())
                <tr><td colspan="{{ $days + 6 }}" class="px-4 py-12 text-center text-slate-400">Tidak ada pegawai.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $staffList->links() }}</div>
    </div>
</div>

{{-- Modal: Set Attendance --}}
<div id="attModal" class="modal-overlay hidden" onclick="closeAttModal()">
    <div class="modal-content max-w-xs" onclick="event.stopPropagation()">
        <h3 class="text-sm font-bold text-slate-800 mb-3">Isi Absensi</h3>
        <div class="text-xs text-slate-500 mb-3" id="attDateLabel"></div>
        <form id="attForm" method="POST" action="{{ route('staff.attendance.store') }}" class="space-y-2">
            @csrf
            <input type="hidden" name="staff_id" id="attStaffId">
            <input type="hidden" name="tanggal" id="attTanggal">
            <div class="grid grid-cols-3 gap-1.5" id="attStatusOptions">
                @foreach(['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alfa'=>'Alfa'] as $val => $label)
                <button type="button"
                    onclick="submitAttendance('{{ $val }}')"
                    class="text-center py-2.5 rounded-lg border text-xs font-semibold transition-all hover:scale-105 active:scale-95 cursor-pointer {{ 
                        $val === 'hadir' ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-200' :
                        ($val === 'terlambat' ? 'border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-200' :
                        ($val === 'izin' ? 'border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-200' :
                        ($val === 'sakit' ? 'border-orange-300 bg-orange-50 text-orange-700 hover:bg-orange-200' :
                        'border-red-300 bg-red-50 text-red-700 hover:bg-red-200')))
                    }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <input name="keterangan" id="attKeterangan" placeholder="Keterangan (opsional)" class="form-input text-xs w-full border border-slate-300 rounded-lg py-2.5 px-3 focus:border-accent focus:ring-1 focus:ring-accent-200">
            <button type="button" onclick="closeAttModal()" class="w-full text-center text-xs text-slate-400 hover:text-slate-600 pt-1">Batal</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const STATUS_LABELS = {
    hadir: 'Hadir', terlambat: 'Terlambat', izin: 'Izin', sakit: 'Sakit', alfa: 'Alfa'
};

function setAttendance(staffId, tanggal, currentStatus) {
    document.getElementById('attStaffId').value = staffId;
    document.getElementById('attTanggal').value = tanggal;
    var statusText = currentStatus ? ' (saat ini: ' + (STATUS_LABELS[currentStatus] || currentStatus) + ')' : '';
    document.getElementById('attDateLabel').textContent = 'Tanggal: ' + tanggal + statusText;
    document.getElementById('attKeterangan').value = '';
    document.getElementById('attModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function submitAttendance(status) {
    // Set status ke hidden input lalu submit form
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'status';
    input.value = status;
    var form = document.getElementById('attForm');
    // Remove old status input if any
    form.querySelectorAll('input[name=status]').forEach(el => el.remove());
    form.appendChild(input);
    form.submit();
}

function closeAttModal() {
    document.getElementById('attModal').classList.remove('show');
    document.body.style.overflow = '';
}

function bulkFill() {
    const status = document.getElementById('bulkStatus').value;
    const tanggal = document.querySelector('#bulkForm [name=tanggal]').value;
    if (!tanggal) return;
    showConfirm('Isi semua pegawai dengan status "' + (STATUS_LABELS[status] || status) + '" untuk tanggal ' + tanggal + '?', 'Isi Massal', 'Ya, Isi', function() {
        var form = document.getElementById('bulkForm');
        form.querySelectorAll('input[name^="records"]').forEach(el => el.remove());
        @foreach($staffList as $staff)
        form.insertAdjacentHTML('beforeend', 
            '<input type="hidden" name="records[{{ $loop->index }}][staff_id]" value="{{ $staff->id }}">' +
            '<input type="hidden" name="records[{{ $loop->index }}][status]" value="' + status + '">');
        @endforeach
        form.submit();
    });
}
</script>
@endpush
