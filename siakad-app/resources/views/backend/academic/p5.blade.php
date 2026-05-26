@extends('layouts.backend')

@section('title', 'Proyek P5')
@section('page_title', 'Proyek Penguatan Profil Pelajar Pancasila')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm"><div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal:</span></div><ul class="list-disc list-inside text-xs space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="flex justify-between items-center mb-5">
    <h2 class="text-sm text-slate-500">{{ $projects->total() }} proyek</h2>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Proyek</button>
</div>

@php
$projectsMap = [];
foreach($projects as $p) {
    $projectsMap[$p->id] = [
        'semester_id' => $p->semester_id,
        'tema' => $p->tema,
        'judul' => $p->judul,
        'deskripsi' => $p->deskripsi ?? '',
        'class_ids' => $p->class_ids,
        'tanggal_mulai' => $p->tanggal_mulai ? $p->tanggal_mulai->format('Y-m-d') : '',
        'tanggal_selesai' => $p->tanggal_selesai ? $p->tanggal_selesai->format('Y-m-d') : '',
    ];
}
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@forelse($projects as $p)
<div class="group/card relative bg-white rounded-2xl border transition {{ $projectId==$p->id ? 'border-accent ring-1 ring-accent-200' : 'border-slate-100' }} hover:border-accent-100 hover:shadow-sm">
    <a href="?project_id={{ $p->id }}" class="block p-5">
        <div class="flex items-start justify-between mb-2">
            <span class="px-2.5 py-1 text-xs rounded-lg bg-fuchsia-50 text-fuchsia-600 font-bold">{{ $p->tema }}</span>
            <span class="px-2 py-1 text-xs rounded-full {{ $p->status=='selesai'?'bg-emerald-50 text-emerald-600':'bg-amber-50 text-amber-600' }}">{{ $p->status }}</span>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1">{{ $p->judul }}</h3>
        <p class="text-xs text-slate-400 line-clamp-2">{{ $p->deskripsi }}</p>
        <div class="flex gap-3 mt-3 text-xs text-slate-400">
            <span><i data-lucide="school" class="w-3 h-3 inline"></i> {{ is_array($p->class_ids) ? count($p->class_ids) : 0 }} kelas</span>
            <span><i data-lucide="calendar" class="w-3 h-3 inline"></i> {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}</span>
        </div>
    </a>
    <div class="absolute top-3 right-3 flex gap-1 opacity-0 group-hover/card:opacity-100 transition">
        <button onclick="event.preventDefault();event.stopPropagation();editProjectModal('{{ $p->id }}')" class="p-2 rounded-lg bg-white border hover:bg-slate-50 text-slate-400 hover:text-accent transition" title="Edit"><i data-lucide="pencil" class="w-4 h-4"></i></button>
        <form method="POST" action="{{ route('academic.p5.project.destroy', $p->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus proyek ini?', 'Hapus Proyek', 'Ya, Hapus', () => this.submit());" class="inline-block"><button type="submit" onclick="event.stopPropagation()" class="p-2 rounded-lg bg-white border hover:bg-red-50 text-slate-400 hover:text-red-600 transition" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
    </div>
</div>
@empty
<div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-100"><i data-lucide="sparkles" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i><p class="text-slate-400">Belum ada proyek P5.</p></div>
@endforelse
</div>
<div class="mt-4">{{ $projects->links() }}</div>

{{-- Detail --}}
@if($projectId)
<div class="mt-6 bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="font-semibold text-slate-800">Penilaian P5</h3>
        <button onclick="openAssessModal()" class="px-4 py-2 rounded-xl btn-accent text-white text-xs font-medium"><i data-lucide="plus" class="w-3.5 h-3.5 inline mr-1"></i> Tambah Nilai</button>
    </div>
    <table class="w-full text-sm"><thead><tr class="bg-slate-50">
        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Siswa</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Akhlak Mulia</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Bhinneka</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Kreatif</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Mandiri</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Gotong Royong</th>
        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Bernalar</th>
    </tr></thead>
    <tbody class="divide-y divide-slate-50">
    @forelse($projectStudents as $student)
    @php $a = $assessments[$student->id] ?? null; @endphp
    <tr>
        <td class="px-4 py-3 font-medium text-slate-800">{{ $student->nama_lengkap }}</td>
        @foreach(['dimensi_1','dimensi_2','dimensi_3','dimensi_4','dimensi_5','dimensi_6'] as $dim)
        <td class="px-2 py-3 text-center"><span class="px-2 py-1 text-xs rounded-full {{ $a?->$dim ? ($a->$dim=='MB'||$a->$dim=='BSH'||$a->$dim=='SB'?'bg-emerald-50 text-emerald-600':'bg-slate-50 text-slate-500'):'text-slate-300' }}">{{ $a?->$dim ?? '-' }}</span></td>
        @endforeach
    </tr>
    @empty
    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada penilaian.</td></tr>
    @endforelse
    </tbody></table>
    {{-- Keterangan Rubrik P5 --}}
    <div class="px-5 py-3 bg-slate-50/50 border-t grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-full flex items-center justify-center bg-red-100 text-red-700 font-bold text-[11px]">BB</span><div><p class="font-semibold text-slate-700">Belum Berkembang</p><p class="text-slate-400 text-[10px]">Peserta didik belum menunjukkan indikator dimensi P5</p></div></div>
        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-100 text-amber-700 font-bold text-[11px]">MB</span><div><p class="font-semibold text-slate-700">Mulai Berkembang</p><p class="text-slate-400 text-[10px]">Peserta didik mulai menunjukkan namun belum konsisten</p></div></div>
        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-700 font-bold text-[11px]">BSH</span><div><p class="font-semibold text-slate-700">Berkembang Sesuai Harapan</p><p class="text-slate-400 text-[10px]">Peserta didik sudah menunjukkan secara konsisten</p></div></div>
        <div class="flex items-center gap-2"><span class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-100 text-blue-700 font-bold text-[11px]">SB</span><div><p class="font-semibold text-slate-700">Sangat Berkembang</p><p class="text-slate-400 text-[10px]">Peserta didik menunjukkan melampaui harapan</p></div></div>
    </div>
</div>
@endif

{{-- MODAL PROYEK --}}
<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold" id="modTitle">Tambah Proyek</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form id="modForm" method="POST" action="{{ route('academic.p5.project.store') }}" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="_method" id="modMethod" value="POST">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label><select name="semester_id" id="modSemester" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($semesters as $s)<option value="{{ $s->id }}" {{ $s->is_active?'selected':'' }}>{{ $s->name }} — {{ $s->academicYear->year_label ?? '' }}</option>@endforeach</select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tema</label><input name="tema" id="modTema" placeholder="Gaya Hidup Berkelanjutan" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul Proyek</label><input name="judul" id="modJudul" placeholder="Judul Proyek P5" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas (bisa pilih lebih dari satu)</label><select name="class_ids[]" id="modClasses" multiple required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200" size="4">@foreach($classes as $k)<option value="{{ $k->id }}">{{ $k->code }} - {{ $k->name }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Mulai</label><input name="tanggal_mulai" id="modTglMulai" type="date" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Selesai</label><input name="tanggal_selesai" id="modTglSelesai" type="date" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" id="modDeskripsi" rows="2" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>

{{-- MODAL PENILAIAN --}}
@if($projectId)
<div class="modal-overlay" id="assessMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Penilaian P5</h3><button onclick="closeAssess()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('academic.p5.assessment.store') }}" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="p5_project_id" value="{{ $projectId }}">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Siswa</label><select name="student_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($projectStudents as $s)<option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->nis }})</option>@endforeach</select></div>
    <div class="grid grid-cols-3 gap-3">
        @foreach(['dimensi_1'=>'Akhlak Mulia','dimensi_2'=>'Bhinneka','dimensi_3'=>'Kreatif'] as $k=>$l)
        <div><label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ $l }}</label><select name="{{ $k }}" class="w-full px-2 py-2 rounded-lg border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option><option value="BB">BB</option><option value="MB">MB</option><option value="BSH">BSH</option><option value="SB">SB</option></select></div>
        @endforeach
    </div>
    <div class="grid grid-cols-3 gap-3">
        @foreach(['dimensi_4'=>'Mandiri','dimensi_5'=>'Gotong Royong','dimensi_6'=>'Bernalar Kritis'] as $k=>$l)
        <div><label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ $l }}</label><select name="{{ $k }}" class="w-full px-2 py-2 rounded-lg border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option><option value="BB">BB</option><option value="MB">MB</option><option value="BSH">BSH</option><option value="SB">SB</option></select></div>
        @endforeach
    </div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeAssess()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endif
@endsection

@push('scripts')
<script>
var p5ProjectsMap = @json($projectsMap);

function openModal() {
    document.getElementById('modForm').action = '{{ route("academic.p5.project.store") }}';
    document.getElementById('modMethod').value = 'POST';
    document.getElementById('modTitle').textContent = 'Tambah Proyek';
    document.getElementById('modForm').reset();
    document.getElementById('mod').classList.add('show');
}

function editProjectModal(id) {
    var p = p5ProjectsMap[id];
    if (!p) return;
    document.getElementById('modForm').action = '{{ url("/backend/academic/p5/projects") }}/' + id;
    document.getElementById('modMethod').value = 'PUT';
    document.getElementById('modTitle').textContent = 'Edit Proyek';
    document.getElementById('modSemester').value = p.semester_id;
    document.getElementById('modTema').value = p.tema;
    document.getElementById('modJudul').value = p.judul;
    document.getElementById('modDeskripsi').value = p.deskripsi || '';
    if (p.tanggal_mulai) document.getElementById('modTglMulai').value = p.tanggal_mulai;
    if (p.tanggal_selesai) document.getElementById('modTglSelesai').value = p.tanggal_selesai;
    var sel = document.getElementById('modClasses');
    var classIds = p.class_ids || [];
    for (var i = 0; i < sel.options.length; i++) {
        sel.options[i].selected = classIds.indexOf(sel.options[i].value) !== -1;
    }
    document.getElementById('mod').classList.add('show');
}

function closeMod() {
    document.getElementById('mod').classList.remove('show');
    document.getElementById('modForm').reset();
    document.getElementById('modMethod').value = 'POST';
    document.getElementById('modTitle').textContent = 'Tambah Proyek';
    document.getElementById('modForm').action = '{{ route("academic.p5.project.store") }}';
}

function openAssessModal() { document.getElementById('assessMod').classList.add('show'); }
function closeAssess() { document.getElementById('assessMod').classList.remove('show'); }

document.getElementById('mod').addEventListener('click', function(e) {
    if (e.target === this) closeMod();
});
@if($projectId)
document.getElementById('assessMod').addEventListener('click', function(e) {
    if (e.target === this) closeAssess();
});
@endif
</script>
@endpush
