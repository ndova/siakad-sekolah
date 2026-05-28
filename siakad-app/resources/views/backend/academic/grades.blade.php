@extends('layouts.backend')

@section('title', 'Input Nilai')
@section('page_title', 'Input Nilai Siswa')

@push('styles')
<style>
.val-input{width:70px;padding:6px 8px;text-align:center;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;transition:border-color .15s}
.val-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent)}
.val-cell{padding:4px 2px;text-align:center}
.val-input.has-value{background:#f0fdf4;border-color:#86efac}
.tp-header{writing-mode:vertical-rl;text-orientation:mixed;transform:rotate(180deg);padding:8px 4px;font-size:10px;min-height:80px;max-height:120px}
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm"><div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal:</span></div><ul class="list-disc list-inside text-xs space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

{{-- Filter --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas - Mapel</label>
            <select name="class_subject_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                <option value="">Pilih Kelas-Mapel</option>
                @foreach($classSubjects as $cs)<option value="{{ $cs->id }}" {{ $classSubjectId==$cs->id?'selected':'' }}>{{ $cs->schoolClass->code ?? '' }} — {{ $cs->subject->name ?? '' }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label>
            <select name="semester_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                @foreach($semesters as $s)<option value="{{ $s->id }}" {{ $semesterId==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Nilai</label>
            <select name="jenis_nilai" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
                <option value="uh" {{ $jenisNilai=='uh'?'selected':'' }}>UH (Ulangan Harian)</option>
                <option value="sts" {{ $jenisNilai=='sts'?'selected':'' }}>STS (Sumatif Tengah Semester)</option>
                <option value="sas" {{ $jenisNilai=='sas'?'selected':'' }}>SAS (Sumatif Akhir Semester)</option>
                <option value="tugas" {{ $jenisNilai=='tugas'?'selected':'' }}>Tugas</option>
                <option value="p5" {{ $jenisNilai=='p5'?'selected':'' }}>P5</option>
            </select>
        </div>
    </div>
</form>

@if($classSubjectId)
{{-- AI Insight: Statistik Kelas --}}
<div id="aiGradeInsight" class="mb-4 hidden">
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-100 text-sm text-slate-600">
        <i data-lucide="sparkles" class="w-4 h-4 text-violet-500 animate-pulse"></i>
        <span id="aiGradeText" class="text-slate-600">Memuat data analitik...</span>
    </div>
</div>

{{-- Grade Input Table --}}
<form method="POST" action="{{ route('academic.grades.bulk') }}" id="gradeForm">
@csrf
<input type="hidden" name="class_subject_id" value="{{ $classSubjectId }}">
<input type="hidden" name="semester_id" value="{{ $semesterId }}">
<input type="hidden" name="jenis_nilai" value="{{ $jenisNilai }}">

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <div class="p-5 border-b flex justify-between items-center flex-wrap gap-3">
        <div>
            <h3 class="font-semibold text-slate-800">Daftar Nilai</h3>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $students->count() }} siswa · {{ $tps->count() }} TP ·
                <span class="font-medium text-slate-500">{{ strtoupper($jenisNilai) }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="resetAll()" class="px-4 py-2 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 inline mr-1"></i> Reset
            </button>
            <button type="submit" class="px-5 py-2.5 rounded-xl btn-accent text-white text-sm font-semibold">
                <i data-lucide="save" class="w-4 h-4 inline mr-1.5"></i> Simpan Semua
            </button>
        </div>
    </div>

    {{-- Tabel Nilai --}}
    <table class="w-full text-sm" id="gradeTable">
        <thead>
            <tr class="bg-slate-50">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase sticky left-0 bg-slate-50 z-10">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIS</th>
                @foreach($tps as $tp)
                <th class="px-2 py-3 text-center w-20" title="{{ $tp->learningObjective->description ?? '' }}">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase">{{ $tp->learningObjective->code ?? $tp->code }}</span>
                </th>
                @endforeach
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Rata²</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($students as $student)
            <tr class="hover:bg-slate-50/30 student-row">
                <td class="px-4 py-3 font-medium text-slate-800 sticky left-0 bg-white hover:bg-slate-50/30">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-[11px]">
                            {{ strtoupper(substr($student->nama_lengkap,0,1)) }}
                        </div>
                        <span>{{ $student->nama_lengkap }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $student->nis }}</td>

                @php $studentTotal = 0; $studentCount = 0; @endphp
                @foreach($tps as $tp)
                    @php
                        // Key: student_id + learning_objective_id (bukan pivot ID)
                        $loId = $tp->learning_objective_id;
                        $key  = $student->id . '_' . $loId;
                        $grade = $grades->get($key);
                        $nilai = $grade->nilai ?? null;
                        if ($nilai !== null) {
                            $studentTotal += (float) $nilai;
                            $studentCount++;
                        }
                    @endphp
                <td class="val-cell">
                    <input type="number"
                           name="nilai[{{ $student->id }}][{{ $tp->id }}]"
                           value="{{ old('nilai.'.$student->id.'.'.$tp->id, $nilai) }}"
                           min="0" max="100" step="0.1"
                           class="val-input {{ $nilai !== null ? 'has-value' : '' }}"
                           placeholder="-"
                           data-student="{{ $student->id }}"
                           data-tp="{{ $tp->id }}">
                </td>
                @endforeach

                <td class="rata-cell px-4 py-3 text-center font-mono font-semibold {{ ($studentCount>0 ? (($studentTotal/$studentCount)>=75?'text-emerald-600':'text-red-500') : 'text-slate-400') }}"
                    data-student-avg="{{ $student->id }}">
                    {{ $studentCount > 0 ? number_format($studentTotal / $studentCount, 1) : '-' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ 3+$tps->count() }}" class="px-4 py-16 text-center text-slate-400">
                <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 text-slate-200"></i>
                Tidak ada siswa di kelas ini.
            </td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Bottom info --}}
    <div class="p-4 border-t bg-slate-50/50">
        <p class="text-xs text-slate-400 text-center">
            <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
            Input nilai 0–100. Kosongkan cell untuk menghapus. Rata² otomatis terhitung.
        </p>
    </div>
</div>
</form>
@else
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Pilih <strong>Kelas-Mapel</strong> untuk mulai input nilai.</p>
</div>
@endif
@endsection

@push('scripts')
<script>
// ─── Input validation (0–100) ──────────────────────────────
document.querySelectorAll('.val-input').forEach(inp => {
    inp.addEventListener('change', function() {
        let v = parseFloat(this.value);
        if (isNaN(v)) { this.value = ''; this.classList.remove('has-value'); }
        else {
            if (v < 0) this.value = 0;
            if (v > 100) this.value = 100;
            this.classList.toggle('has-value', this.value !== '');
        }
        updateAverage(this);
    });
    inp.addEventListener('input', function() {
        this.classList.toggle('has-value', this.value !== '');
        updateAverage(this);
    });
});

// ─── Auto-calculate Rata² per row ────────────────────────────
function updateAverage(input) {
    const studentId = input.dataset.student;
    if (!studentId) return;
    // Cari semua input untuk siswa ini
    const rowInputs = document.querySelectorAll(`.val-input[data-student="${studentId}"]`);
    let total = 0, count = 0;
    rowInputs.forEach(inp => {
        const v = parseFloat(inp.value);
        if (!isNaN(v)) { total += v; count++; }
    });
    // Update cell rata²
    const avgCell = document.querySelector(`.rata-cell[data-student-avg="${studentId}"]`);
    if (avgCell) {
        if (count > 0) {
            const avg = total / count;
            avgCell.textContent = avg.toFixed(1);
            avgCell.classList.remove('text-slate-400');
            avgCell.classList.add(avg >= 75 ? 'text-emerald-600' : 'text-red-500');
        } else {
            avgCell.textContent = '-';
            avgCell.classList.remove('text-emerald-600', 'text-red-500');
            avgCell.classList.add('text-slate-400');
        }
    }
}

// ─── Reset all inputs ───────────────────────────────────────
function resetAll() {
    showConfirm('Reset semua nilai yang belum disimpan?', 'Reset Nilai', 'Ya, Reset', function() {
        document.querySelectorAll('.val-input').forEach(inp => {
            inp.value = '';
            inp.classList.remove('has-value');
        });
        document.querySelectorAll('.rata-cell').forEach(cell => {
            cell.textContent = '-';
            cell.classList.remove('text-emerald-600', 'text-red-500');
            cell.classList.add('text-slate-400');
        });
    });
}

// ─── Keyboard navigation ────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.classList.contains('val-input')) {
        e.preventDefault();
        let cells = Array.from(document.querySelectorAll('.val-input'));
        let idx = cells.indexOf(e.target);
        if (idx >= 0 && idx < cells.length - 1) {
            cells[idx + 1].focus();
            cells[idx + 1].select();
        }
    }
});

// ─── AI Grade Insight ───────────────────────────────────────
(function() {
    var csId = {{ $classSubjectId ?: 'null' }};
    if (!csId) return;
    var container = document.getElementById('aiGradeInsight');
    var textEl = document.getElementById('aiGradeText');
    if (!container || !textEl) return;
    container.classList.remove('hidden');
    fetch('{{ route("ai.rekomendasi-nilai") }}?class_subject_id=' + csId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            var msg = '📊 Rata-rata kelas: <strong>' + res.rata_kelas + '</strong> | ';
            msg += 'Tertinggi: <strong>' + res.tertinggi + '</strong> | ';
            msg += 'Terendah: <strong>' + res.terendah + '</strong> | ';
            msg += 'Total nilai: <strong>' + res.total + '</strong>';
            textEl.innerHTML = msg;
        } else {
            container.classList.add('hidden');
        }
    })
    .catch(function() { container.classList.add('hidden'); });
})();

// ─── Auto-refresh icon setelah load ─────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
