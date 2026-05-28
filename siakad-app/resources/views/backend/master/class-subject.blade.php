@extends('layouts.backend')

@section('title', 'Pemetaan Kelas-Mapel')
@section('page_title', 'Pemetaan Kelas - Mata Pelajaran - Guru')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border-accent-100 text-accent text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm"><div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal:</span></div><ul class="list-disc list-inside text-xs space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="flex justify-between items-center mb-5">
    <form method="GET" class="flex gap-2 filter-form">
        <select name="class_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">
            <option value="">Pilih Kelas</option>
            @foreach($classes as $k)<option value="{{ $k->id }}" {{ $classId==$k->id?'selected':'' }}>{{ $k->code }} - {{ $k->name }}</option>@endforeach
        </select>
    </form>
    @if($classId)
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Mapel</button>
    @endif
</div>

@if(!$classId)
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="git-branch" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Pilih kelas terlebih dahulu untuk melihat pemetaan.</p>
</div>
@else
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left">
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Mapel</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Guru Pengampu</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">JP / Minggu</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Semester</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-50">
@forelse($mappings as $m)
<tr class="hover:bg-slate-50/50">
    <td class="px-5 py-3.5"><span class="font-medium text-slate-800">{{ $m->subject->name ?? '-' }}</span><br><span class="text-xs text-slate-400">{{ $m->subject?->code }}</span></td>
    <td class="px-5 py-3.5"><span class="text-slate-600">{{ $m->teacher->name ?? 'Belum diatur' }}</span></td>
    <td class="px-5 py-3.5 font-mono text-sm">{{ $m->jam_per_minggu ?? '-' }}</td>
    <td class="px-5 py-3.5"><span class="px-2 py-1 text-xs rounded-full {{ $m->semester?->is_active?'bg-accent-50 text-accent':'bg-slate-100' }}">{{ $m->semester?->name ?? '-' }}</span></td>
    <td class="px-5 py-3.5">
        <div class="flex items-center gap-1">
            <button onclick="editClassSubjectModal('{{ $m->id }}','{{ $m->subject_id }}','{{ $m->semester_id ?? '' }}','{{ $m->teacher_id ?? '' }}','{{ $m->jam_per_minggu ?? 2 }}')" class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent"><i data-lucide="pencil" class="w-4 h-4"></i></button>
            <form method="POST" action="{{ route('master.class-subject.delete', $m->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus pemetaan ini?', 'Hapus Pemetaan', 'Ya, Hapus', () => this.submit());">@csrf<button class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Belum ada pemetaan.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $mappings->links() }}</div>
@endif

{{-- MODAL --}}
<div class="modal-overlay" id="mod"><div class="modal-box">
<div class="p-5 border-b flex justify-between"><h3 class="font-semibold" id="modTitle">Tambah Mapel ke Kelas</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" id="modForm" action="" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="_method" id="modMethod" value="POST">
    <input type="hidden" name="class_id" value="{{ $classId }}">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label><select name="semester_id" id="semesterSelect" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"><option value="">Pilih Semester</option>@foreach($semesters as $sem)<option value="{{ $sem->id }}">{{ $sem->academicYear?->code }} - {{ $sem->name }}</option>@endforeach</select></div>
    <div id="subjectRow"><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mata Pelajaran</label><select name="subject_id" id="subjectSelect" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200">@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->code }} - {{ $s->name }}</option>@endforeach</select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Guru Pengampu</label><select name="teacher_id" id="teacherSelect" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"><option value="">Pilih Guru</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">JP / Minggu</label><input name="jam_per_minggu" id="jpInput" type="number" min="1" max="40" value="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Batal</button></div>
</form>
</div></div>
@endsection

@push('scripts')
<script>
function openModal(){
    document.getElementById('modTitle').textContent = 'Tambah Mapel ke Kelas';
    document.getElementById('modForm').action = '{{ route("master.class-subject.store") }}';
    document.getElementById('modMethod').value = 'POST';
    document.getElementById('subjectSelect').disabled = false;
    document.getElementById('subjectSelect').required = true;
    document.getElementById('subjectSelect').value = '';
    document.getElementById('semesterSelect').value = '';
    document.getElementById('teacherSelect').required = true;
    document.getElementById('teacherSelect').value = '';
    document.getElementById('jpInput').value = '2';
    document.getElementById('mod').classList.add('show');
}
function closeMod(){
    document.getElementById('mod').classList.remove('show');
    // Reset method back to POST for next add
    document.getElementById('modMethod').value = 'POST';
}
function editClassSubjectModal(id, subjectId, semesterId, teacherId, jp){
    document.getElementById('modTitle').textContent = 'Edit Mapel';
    document.getElementById('modForm').action = '{{ url("/backend/master/class-subject") }}/' + id;
    document.getElementById('modMethod').value = 'PUT';
    // Enable subject — bisa rubah mata pelajaran
    document.getElementById('subjectSelect').disabled = false;
    document.getElementById('subjectSelect').required = true;
    document.getElementById('subjectSelect').value = subjectId;
    document.getElementById('semesterSelect').value = semesterId || '';
    document.getElementById('teacherSelect').required = true;
    document.getElementById('teacherSelect').value = teacherId || '';
    document.getElementById('jpInput').value = jp || 2;
    document.getElementById('mod').classList.add('show');
}
document.getElementById('mod').addEventListener('click',function(e){if(e.target===this)closeMod()})
</script>
@endpush
