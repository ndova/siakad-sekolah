@extends('layouts.backend')

@section('title', 'Input / Edit Rapor — ' . $student->nama_lengkap)
@section('page_title', 'Input / Edit Rapor')

@push('styles')
<style>
.val-input { width: 85px; padding: 8px 10px; text-align: center; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; outline: none; transition: border-color .15s; }
.val-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent); }
.desk-input { min-width: 300px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; outline: none; transition: border-color .15s; }
.desk-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent); }
.pred-select { padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; outline: none; text-align: center; width: 75px; }
.pred-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent); }
.locked-row { background: #fefce8; opacity: 0.75; }
</style>
@endpush

@section('content')

{{-- Status message --}}
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
</div>
@endif

{{-- Action bar --}}
<div class="flex items-center gap-3 mb-5 flex-wrap">
    <a href="{{ route('academic.reports', ['class_id'=>$class->id, 'semester_id'=>$semesterId]) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
    </a>

    {{-- Semester selector --}}
    <form method="GET" class="inline-flex items-center gap-2">
        <input type="hidden" name="semester_id" id="semesterHidden">
        <select class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"
                onchange="document.getElementById('semesterHidden').value=this.value; this.form.submit()">
            @foreach($semesters as $s)
                <option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Student info card --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex items-center gap-4 flex-wrap">
        <div class="w-12 h-12 rounded-xl bg-accent-100 flex items-center justify-center text-accent font-bold text-lg">
            {{ strtoupper(substr($student->nama_lengkap, 0, 1)) }}
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-lg">{{ $student->nama_lengkap }}</h3>
            <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5">
                <span>NIS: <strong class="text-slate-700 font-mono">{{ $student->nis }}</strong></span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span>Kelas: <strong class="text-slate-700">{{ $class->code }} — {{ $class->name }}</strong></span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span>Semester: <strong class="text-slate-700">{{ $semester->name }}</strong></span>
            </div>
        </div>
    </div>
</div>

{{-- Report input form --}}
<form method="POST" action="{{ route('academic.reports.store') }}">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <input type="hidden" name="semester_id" value="{{ $semesterId }}">
    <input type="hidden" name="class_id" value="{{ $class->id }}">

    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
        <div class="p-5 border-b flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">
                Nilai Rapor — {{ count($classSubjects) }} Mata Pelajaran
            </h3>
            <button type="submit" class="px-5 py-2.5 rounded-xl btn-accent text-white text-sm font-semibold inline-flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Rapor
            </button>
        </div>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Mata Pelajaran</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-32">KKM</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-32">Nilai Akhir</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-32">Predikat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Deskripsi Capaian Kompetensi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-24">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @foreach($classSubjects as $index => $cs)
                    @php
                        $report = $existingReports->get($cs->id);
                        $isLocked = $report?->is_locked ?? false;
                    @endphp
                    <tr class="hover:bg-slate-50/30 {{ $isLocked ? 'locked-row' : '' }}">
                        <td class="px-4 py-3 text-center text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-700">{{ $cs->subject->name }}</span>
                            @if($isLocked)
                                <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-medium">
                                    <i data-lucide="lock" class="w-3 h-3"></i> Terkunci
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-slate-500">{{ $cs->kkm }}</td>
                        <td class="px-4 py-3 text-center">
                            <input type="number"
                                   name="nilai_akhir[{{ $cs->id }}]"
                                   value="{{ old('nilai_akhir.'.$cs->id, $report->nilai_akhir ?? '') }}"
                                   min="0" max="100" step="0.1"
                                   class="val-input"
                                   placeholder="-"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <select name="predikat[{{ $cs->id }}]" class="pred-select" {{ $isLocked ? 'disabled' : '' }}>
                                <option value="">-</option>
                                @foreach(['A','B','C','D'] as $p)
                                    <option value="{{ $p }}" {{ (old('predikat.'.$cs->id, $report->predikat ?? '')) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <input type="text"
                                   name="deskripsi_cp[{{ $cs->id }}]"
                                   value="{{ old('deskripsi_cp.'.$cs->id, $report->deskripsi_cp ?? '') }}"
                                   class="desk-input w-full"
                                   placeholder="Deskripsi capaian kompetensi..."
                                   maxlength="1000"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isLocked)
                                <span class="px-2 py-1 text-[11px] rounded-full bg-amber-50 text-amber-600 font-medium">Dikunci</span>
                            @elseif($report)
                                <span class="px-2 py-1 text-[11px] rounded-full bg-blue-50 text-blue-600 font-medium">Draft</span>
                            @else
                                <span class="px-2 py-1 text-[11px] rounded-full bg-slate-50 text-slate-400 font-medium">Baru</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bottom actions --}}
        <div class="p-5 border-t flex justify-between items-center bg-slate-50/50">
            <p class="text-xs text-slate-400">
                <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                Nilai yang sudah dikunci tidak dapat diubah. Kosongkan semua field pada baris untuk menghapus entri rapor.
            </p>
            <button type="submit" class="px-5 py-2.5 rounded-xl btn-accent text-white text-sm font-semibold inline-flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Semua
            </button>
        </div>
    </div>
</form>

@endsection
