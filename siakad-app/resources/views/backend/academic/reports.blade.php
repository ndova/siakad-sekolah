@extends('layouts.backend')

@section('title', 'Rapor Siswa')
@section('page_title', 'Rapor Siswa')

@push('styles')
<style>
.rapor-locked { background: #fefce8; }
.rapor-draft { background: #f8fafc; }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}</div>
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
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label>
            <select name="semester_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                @foreach($semesters as $s)<option value="{{ $s->id }}" {{ $semesterId==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
    </div>
</form>

@if($classId && $students->count())
{{-- Batch Action Forms --}}
<form id="lockForm" method="POST" action="{{ route('academic.reports.lock') }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $classId }}">
    <input type="hidden" name="semester_id" value="{{ $semesterId }}">
</form>
<form id="unlockForm" method="POST" action="{{ route('academic.reports.unlock') }}">
    @csrf
    <input type="hidden" name="semester_id" value="{{ $semesterId }}">
</form>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    {{-- Header Actions --}}
    <div class="p-5 border-b flex justify-between items-center flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <h3 class="font-semibold text-slate-800">Daftar Rapor</h3>
            <span class="text-xs text-slate-400">{{ $students->total() }} siswa</span>
        </div>
        <div class="flex items-center gap-2">
            {{-- Select All --}}
            <label class="flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer select-none px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="rounded border-slate-300 text-accent focus:ring-accent-200">
                Pilih Semua
            </label>
            {{-- Kunci Terpilih --}}
            <button type="button" onclick="submitBatch('lock')" class="px-4 py-2 rounded-xl bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition inline-flex items-center gap-1.5" title="Kunci rapor siswa terpilih">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i> Kunci Terpilih
            </button>
            {{-- Buka Kunci Terpilih --}}
            <button type="button" onclick="submitBatch('unlock')" class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-xs font-semibold hover:bg-emerald-600 transition inline-flex items-center gap-1.5" title="Buka kunci rapor siswa terpilih">
                <i data-lucide="unlock" class="w-3.5 h-3.5"></i> Buka Kunci
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50">
                <th class="px-4 py-3 text-center w-10">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase">#</span>
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Mapel</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Rata²</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
        @foreach($students as $student)
        @php
            $studentReports = $reports[$student->id] ?? collect();
            $rata = $studentReports->avg('nilai_akhir');
            $isLocked = $studentReports->contains(fn($r) => $r->is_locked);
            $lockedCount = $studentReports->where('is_locked', true)->count();
        @endphp
        <tr class="hover:bg-slate-50/30 {{ $isLocked ? 'rapor-locked' : 'rapor-draft' }}">
            {{-- Checkbox --}}
            <td class="px-4 py-3 text-center">
                <input type="checkbox"
                       name="student_ids[]"
                       value="{{ $student->id }}"
                       form="lockForm"
                       class="student-checkbox rounded border-slate-300 text-accent focus:ring-accent-200">
            </td>
            <td class="px-4 py-3 font-mono text-xs">{{ $student->nis }}</td>
            <td class="px-4 py-3 font-medium text-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent font-bold text-xs">{{ strtoupper(substr($student->nama_lengkap,0,1)) }}</div>
                    {{ $student->nama_lengkap }}
                </div>
            </td>
            <td class="px-4 py-3 text-center text-slate-500">
                <span class="font-medium">{{ $lockedCount }}</span>
                <span class="text-slate-400">/{{ $studentReports->count() }}</span>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="font-mono font-semibold {{ ($rata??0)>=75?'text-emerald-600':'text-red-500' }}">
                    {{ $rata ? number_format($rata,1) : '-' }}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                @if($isLocked)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-100 text-amber-700 font-medium">
                        <i data-lucide="lock" class="w-3 h-3"></i> Terkunci
                    </span>
                @elseif($studentReports->isNotEmpty())
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">
                        <i data-lucide="edit-3" class="w-3 h-3"></i> Draft
                    </span>
                @else
                    <span class="px-2.5 py-1 text-xs rounded-full bg-slate-100 text-slate-400">Belum</span>
                @endif
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-1.5">
                    {{-- Lihat --}}
                    <a href="{{ route('academic.reports.show', ['student'=>$student->id, 'semester_id'=>$semesterId]) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-accent-100 text-accent text-xs font-semibold hover:bg-accent-200 transition" title="Lihat Rapor">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    </a>
                    {{-- Edit (hanya jika belum terkunci) --}}
                    @if(!$isLocked)
                    <a href="{{ route('academic.reports.edit', ['student'=>$student->id, 'semester_id'=>$semesterId]) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-100 text-blue-600 text-xs font-semibold hover:bg-blue-200 transition" title="Edit Rapor">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    </a>
                    @endif
                    {{-- Lock / Unlock Toggle --}}
                    <form method="POST" action="{{ route('academic.reports.toggle-lock', ['student'=>$student->id, 'semester_id'=>$semesterId]) }}" class="inline">
                        @csrf
                        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                        @if($isLocked)
                        <button type="button"
                                onclick="event.preventDefault(); showConfirm('Buka kunci rapor {{ $student->nama_lengkap }}?', 'Buka Kunci Rapor', 'Ya, Buka', () => this.closest('form').submit())"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-100 text-emerald-600 text-xs font-semibold hover:bg-emerald-200 transition" title="Buka Kunci">
                            <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                        </button>
                        @else
                        <button type="button"
                                onclick="event.preventDefault(); showConfirm('Kunci rapor {{ $student->nama_lengkap }}? Nilai akan dihitung otomatis dari data nilai yang ada.', 'Kunci Rapor', 'Ya, Kunci', () => this.closest('form').submit())"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-100 text-amber-600 text-xs font-semibold hover:bg-amber-200 transition" title="Kunci Rapor">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        </button>
                        @endif
                    </form>
                    {{-- Hapus (hanya jika belum terkunci) --}}
                    @if(!$isLocked && $studentReports->isNotEmpty())
                    <form method="POST" action="{{ route('academic.reports.delete', $studentReports->first()->id) }}"
                          onsubmit="event.preventDefault(); showConfirm('Hapus rapor {{ $student->nama_lengkap }}?', 'Hapus Rapor', 'Ya, Hapus', () => this.submit());" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-100 text-red-600 text-xs font-semibold hover:bg-red-200 transition" title="Hapus Rapor">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $students->links() }}</div>

@else
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Pilih kelas untuk melihat daftar rapor.</p>
</div>
@endif
@endsection

@push('scripts')
<script>
// ─── Select All ──────────────────────────────────────────────
function toggleSelectAll(el) {
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.checked = el.checked;
    });
}

// ─── Sync individual checkboxes with "Select All" ────────────
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('student-checkbox')) {
        const all = document.querySelectorAll('.student-checkbox');
        const checked = document.querySelectorAll('.student-checkbox:checked');
        document.getElementById('selectAll').checked = all.length > 0 && checked.length === all.length;
    }
});

// ─── Submit Batch Lock / Unlock ──────────────────────────────
function submitBatch(action) {
    const checked = document.querySelectorAll('.student-checkbox:checked');
    if (checked.length === 0) {
        showToast('Pilih minimal satu siswa terlebih dahulu.', 'error');
        return;
    }

    const form = document.getElementById(action === 'lock' ? 'lockForm' : 'unlockForm');

    // Hapus input student_ids lama
    form.querySelectorAll('input[name="student_ids[]"]').forEach(el => el.remove());

    // Tambahkan yang terpilih ke form yang sesuai
    checked.forEach(cb => {
        const clone = cb.cloneNode(true);
        clone.style.display = 'none';
        clone.checked = true;
        form.appendChild(clone);
    });

    if (action === 'lock') {
        showConfirm('Kunci rapor untuk ' + checked.length + ' siswa terpilih? Nilai akan dihitung otomatis.', 'Kunci Rapor', 'Ya, Kunci', function() { form.submit(); });
        return;
    } else {
        showConfirm('Buka kunci rapor untuk ' + checked.length + ' siswa terpilih?', 'Buka Kunci Rapor', 'Ya, Buka', function() { form.submit(); });
        return;
    }

    form.submit();
}

// ─── Init icons ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
