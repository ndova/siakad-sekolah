@extends('layouts.backend')

@section('title', 'Kurikulum Merdeka')
@section('page_title', 'Kurikulum Merdeka — CP / TP / ATP')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}.tab-btn{border:none;background:none;padding:8px 16px;font-size:13px;cursor:pointer;color:#94a3b8;font-weight:500;border-bottom:2px solid transparent;transition:all .15s}.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:600}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border-accent-100 text-accent text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm"><div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal:</span></div><ul class="list-disc list-inside text-xs space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

{{-- Top Actions --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    <button onclick="openCurrMod()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Kurikulum</button>
    <button onclick="openCPMod()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah CP</button>
    @if($curriculum)
    <button onclick="openTPMod()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah TP</button>
    <button onclick="openATPMod()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> ATP Mapel</button>
    @endif
</div>

{{-- Curriculum List --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-slate-500 uppercase mb-3">Daftar Kurikulum</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($curricula as $cur)
    <a href="?curriculum_id={{ $cur->id }}" class="block bg-white rounded-2xl border p-5 hover:border-accent-100 hover:shadow-sm transition {{ $curriculum && $curriculum->id==$cur->id ? 'border-accent ring-1 ring-accent-200' : 'border-slate-100' }}">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-xl bg-accent-100 flex items-center justify-center"><i data-lucide="target" class="w-4 h-4 text-accent"></i></div>
            <div><span class="font-semibold text-sm text-slate-800">{{ $cur->name }}</span><br><span class="text-xs text-slate-400">{{ $cur->code }}</span></div>
        </div>
        <div class="text-xs text-slate-400 flex gap-3">
            <span><i data-lucide="layers" class="w-3 h-3 inline"></i> {{ $cur->learningOutcomes->count() }} CP</span>
            <span><i data-lucide="list-checks" class="w-3 h-3 inline"></i> {{ $cur->learningOutcomes->pluck('learningObjectives')->flatten()->count() }} TP</span>
        </div>
    </a>
    @empty
    <div class="col-span-full bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">Belum ada kurikulum.</div>
    @endforelse
    </div>
</div>

{{-- CP/TP Detail --}}
@if($curriculum)
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <div class="p-5 border-b flex items-center justify-between">
        <h3 class="font-semibold text-slate-800">{{ $curriculum->name }} — Capaian & Tujuan Pembelajaran</h3>
        <span class="text-xs text-slate-400">{{ $curriculum->learningOutcomes->count() }} CP, {{ $curriculum->learningOutcomes->pluck('learningObjectives')->flatten()->count() }} TP</span>
    </div>
    <div class="p-5 space-y-4">
        @forelse($curriculum->learningOutcomes as $lo)
        <div class="rounded-xl border border-slate-100 bg-slate-50/30 p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-bold text-accent">{{ $lo->code }}</span>
                <span class="px-2 py-0.5 text-[10px] rounded bg-accent-50 text-accent">{{ $lo->phase ?? '-' }}</span>
            </div>
            <p class="text-sm text-slate-700 mb-3">{{ $lo->description }}</p>
            @if($lo->learningObjectives->count())
            <div class="space-y-2 pl-3 border-l-2 border-accent-200">
            @foreach($lo->learningObjectives as $tp)
            <div class="flex items-start gap-2">
                <span class="text-xs font-mono text-accent mt-0.5">{{ $tp->code }}</span>
                <span class="text-xs text-slate-600">{{ $tp->description }}</span>
            </div>
            @endforeach
            </div>
            @endif
        </div>
        @empty
        <p class="text-slate-400 text-sm text-center py-4">Belum ada CP untuk kurikulum ini.</p>
        @endforelse
    </div>
</div>
@endif

{{-- ATP Mapping Section --}}
@if($curriculum && $classSubjects->count())
<div class="bg-white rounded-2xl border border-slate-100 mt-6 p-5">
    <h3 class="font-semibold text-slate-800 mb-4">Alur Tujuan Pembelajaran (ATP) — Pemetaan Mapel</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm"><thead><tr class="bg-slate-50"><th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Kelas</th><th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Mapel</th><th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">Guru</th><th class="px-4 py-2 text-left text-xs font-semibold text-slate-500">TP Terpetakan</th></tr></thead>
    <tbody class="divide-y divide-slate-50">
    @foreach($classSubjects as $cs)
    <tr class="hover:bg-slate-50/50"><td class="px-4 py-3">{{ $cs->schoolClass->code ?? '-' }}</td><td class="px-4 py-3">{{ $cs->subject->name ?? '-' }}</td><td class="px-4 py-3">{{ $cs->teacher->name ?? '-' }}</td><td class="px-4 py-3 text-xs text-slate-500">{{ $cs->learningObjectiveSubjects()->count() ?? 0 }} TP</td></tr>
    @endforeach
    </tbody></table>
    </div>
    <div class="mt-3">{{ $classSubjects->links() }}</div>
</div>
@endif

{{-- MODALS --}}
{{-- Kurikulum --}}
<div class="modal-overlay" id="curMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Kurikulum</h3><button onclick="closeCur()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('academic.curricula.store') }}" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="academic_year_id" value="{{ $activeYearId ?? '' }}">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" placeholder="KUR-MER-2025" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama</label><input name="name" placeholder="Kurikulum Merdeka SMP" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi</label><textarea name="description" rows="2" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeCur()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>

{{-- CP --}}
<div class="modal-overlay" id="cpMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Capaian Pembelajaran</h3><button onclick="closeCP()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('academic.cp.store') }}" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="curriculum_id" value="{{ $curriculum->id ?? '' }}">
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Mata Pelajaran</label>
        <select name="subject_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">
            <option value="">Pilih Mata Pelajaran</option>
            @foreach($subjects as $subj)<option value="{{ $subj->id }}">{{ $subj->code }} — {{ $subj->name }}</option>@endforeach
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" placeholder="CP-BIND-7" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Fase</label><input name="phase" placeholder="D" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi</label><textarea name="description" rows="3" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div class="flex gap-3"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeCP()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>

{{-- TP --}}
<div class="modal-overlay" id="tpMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Tujuan Pembelajaran</h3><button onclick="closeTP()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('academic.tp.store') }}" class="p-5 space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Capaian Pembelajaran</label>
        <select name="learning_outcome_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">
            <option value="">Pilih CP</option>
            @if($curriculum)@foreach($curriculum->learningOutcomes as $lo)<option value="{{ $lo->id }}">{{ $lo->code }} — {{ Str::limit($lo->description, 60) }}</option>@endforeach @endif
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" placeholder="TP-BIND-7.1" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Urutan</label><input name="urutan" type="number" value="1" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi</label><textarea name="description" rows="2" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div class="flex gap-3"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeTP()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>

{{-- ATP --}}
<div class="modal-overlay" id="atpMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Pemetaan ATP (TP ke Mapel)</h3><button onclick="closeATP()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('academic.atp.store') }}" class="p-5 space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Class Subject</label><select name="class_subject_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($classSubjects as $cs)<option value="{{ $cs->id }}">{{ $cs->schoolClass->code ?? '' }} - {{ $cs->subject->code ?? '' }} ({{ $cs->teacher->name ?? '' }})</option>@endforeach</select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tujuan Pembelajaran</label><select name="learning_objective_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@if($curriculum)@foreach($curriculum->learningOutcomes as $lo)@foreach($lo->learningObjectives as $tp)<option value="{{ $tp->id }}">{{ $tp->code }} — {{ Str::limit($tp->description,50) }}</option>@endforeach @endforeach @endif</select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label><select name="semester_id" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($semesters as $sm)<option value="{{ $sm->id }}" {{ $sm->is_active?'selected':'' }}>{{ $sm->name }}</option>@endforeach</select></div>
    <div class="flex gap-3"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeATP()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
function openCurrMod(){document.getElementById('curMod').classList.add('show')} function closeCur(){document.getElementById('curMod').classList.remove('show')}
function openCPMod(){document.getElementById('cpMod').classList.add('show')} function closeCP(){document.getElementById('cpMod').classList.remove('show')}
function openTPMod(){document.getElementById('tpMod').classList.add('show')} function closeTP(){document.getElementById('tpMod').classList.remove('show')}
function openATPMod(){document.getElementById('atpMod').classList.add('show')} function closeATP(){document.getElementById('atpMod').classList.remove('show')}
document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show')}))
</script>
@endpush
