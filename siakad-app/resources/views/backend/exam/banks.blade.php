@extends('layouts.backend')

@section('title', 'Bank Soal')
@section('page_title', 'Bank Soal')

@push('styles')
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}
.modal-overlay.show{display:flex}
.modal-box{background:#fff;border-radius:16px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}
.bank-group-header{position:sticky;top:0;z-index:5}
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

{{-- FILTER BAR --}}
<div class="bg-white rounded-2xl border border-slate-100 p-4 mb-5">
    <form method="GET" class="flex flex-wrap items-end gap-3 filter-form">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label>
            <select name="class_id" id="filterClass" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200 min-w-[200px]">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                <option value="{{ $c->id }}" {{ request('class_id')==$c->id?'selected':'' }}>
                    {{ $c->code }} {{ $c->major ? '('.$c->major->name.')' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Mata Pelajaran</label>
            <select name="subject_id" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200 min-w-[180px]" onchange="this.form.submit()">
                <option value="">Semua Mapel</option>
                @foreach($subjects as $s)
                <option value="{{ $s->id }}" {{ request('subject_id')==$s->id?'selected':'' }}>{{ $s->code }} - {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="search" class="w-4 h-4 inline mr-1"></i>Filter</button>
        @if(request()->anyFilled(['class_id','subject_id']))
        <a href="{{ route('exam.banks') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50"><i data-lucide="x" class="w-4 h-4 inline mr-1"></i>Reset</a>
        @endif
        <button type="button" onclick="openModal()" class="ml-auto inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Bank</button>
    </form>
</div>

{{-- BANK LIST - dikelompokkan per Kelas → Jurusan --}}
@php
$grouped = $banks->groupBy(function($b){
    $kelas = optional($b->schoolClass);
    $major = optional($kelas->major);
    return ($kelas->tingkat ?? '?') . '|' . ($major->name ?? 'Umum');
});
@endphp

@forelse($grouped as $key => $groupBanks)
@php [$tingkat, $jurusan] = explode('|', $key); @endphp
<div class="mb-6">
    <div class="bank-group-header bg-slate-50/80 backdrop-blur rounded-xl px-4 py-2.5 mb-3 border border-slate-100 flex items-center gap-3">
        <span class="px-2.5 py-1 rounded-lg bg-accent-100 text-accent-700 text-xs font-bold">Kelas {{ $tingkat }}</span>
        <span class="text-xs font-medium text-slate-500">Jurusan <span class="text-slate-700">{{ $jurusan }}</span></span>
        <span class="text-xs text-slate-400 ml-auto">{{ $groupBanks->count() }} bank soal</span>
    </div>

    {{-- Sub-kelompok per Mata Pelajaran dalam Kelas+Jurusan yang sama --}}
    @foreach($groupBanks->groupBy('subject_id') as $subjectBanks)
    @php $subject = $subjectBanks->first()->subject; @endphp
    <div class="mb-3 ml-2">
        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-2">
            <i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-400"></i>
            {{ $subject->name ?? 'Tanpa Mapel' }}
            <span class="font-normal text-slate-400">({{ $subjectBanks->count() }})</span>
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($subjectBanks as $bank)
        <a href="{{ route('exam.questions', ['question_bank_id'=>$bank->id]) }}" class="bg-white rounded-xl border border-slate-100 p-4 hover:border-accent-200 hover:shadow-sm transition block">
            <div class="flex items-start gap-2.5 mb-2">
                <div class="w-9 h-9 rounded-lg bg-orange-100 flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-4 h-4 text-orange-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h5 class="font-semibold text-slate-800 text-sm truncate">{{ $bank->name }}</h5>
                    <span class="text-xs text-slate-400">{{ $bank->schoolClass->code ?? '-' }} {{ $bank->subject->code ?? '' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-400">
                <span class="flex items-center gap-1"><i data-lucide="help-circle" class="w-3 h-3"></i> {{ $bank->questions_count ?? 0 }} soal</span>
                <span class="flex items-center gap-1"><i data-lucide="user" class="w-3 h-3"></i> {{ $bank->creator->name ?? '-' }}</span>
            </div>
        </a>
        @endforeach
        </div>
    </div>
    @endforeach
</div>
@empty
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="package" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Belum ada bank soal.</p>
</div>
@endforelse

<div class="mt-4">{{ $banks->links() }}</div>

{{-- CREATE MODAL --}}
<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Bank Soal</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('exam.banks.store') }}" class="p-5 space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Bank</label><input name="name" placeholder="Contoh: Bank Soal UH 1" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas <span class="text-slate-400 font-normal">(otomatis tentukan Jurusan)</span></label>
        <select name="class_id" id="modalClass" required onchange="filterModalSubjects()" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">
            <option value="">Pilih Kelas</option>
            @foreach($classes as $c)
            <option value="{{ $c->id }}" data-major="{{ $c->major->name ?? 'Umum' }}">
                Kelas {{ $c->code }} — Jurusan {{ $c->major->name ?? 'Umum' }}
            </option>
            @endforeach
        </select>
        <p id="modalMajorHint" class="text-xs text-slate-400 mt-1 hidden"></p>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Mata Pelajaran</label>
        <select name="subject_id" id="modalSubject" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">
            <option value="">Pilih Mapel</option>
        </select>
    </div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
const classSubjects = @json($classSubjects);

function openModal() {
    document.getElementById('modalClass').value = '';
    document.getElementById('modalSubject').innerHTML = '<option value="">Pilih Mapel</option>';
    document.getElementById('modalMajorHint').classList.add('hidden');
    document.getElementById('mod').classList.add('show');
}
function closeMod() { document.getElementById('mod').classList.remove('show'); }
document.getElementById('mod').addEventListener('click', function(e){ if(e.target===this) closeMod(); });

function filterModalSubjects() {
    var classId = document.getElementById('modalClass').value;
    var subjectSelect = document.getElementById('modalSubject');
    var hint = document.getElementById('modalMajorHint');
    subjectSelect.innerHTML = '<option value="">Pilih Mapel</option>';

    if (!classId) {
        hint.classList.add('hidden');
        return;
    }

    // Tampilkan jurusan hint
    var selectedOption = document.getElementById('modalClass').selectedOptions[0];
    var major = selectedOption.getAttribute('data-major');
    hint.textContent = 'Jurusan: ' + major;
    hint.classList.remove('hidden');

    // Filter mapel berdasarkan kelas
    var subjects = classSubjects[classId] || [];
    subjects.forEach(function(cs) {
        subjectSelect.innerHTML += '<option value="'+cs.subject_id+'">'+cs.code+' - '+cs.name+'</option>';
    });
}
</script>
@endpush
