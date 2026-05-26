@extends('layouts.backend')

@section('title', 'Daftar Ujian')
@section('page_title', 'Daftar Ujian')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm"><div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal:</span></div><ul class="list-disc list-inside text-xs space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="flex justify-between items-center mb-5">
    <h2 class="text-sm text-slate-500">{{ $exams->total() }} ujian</h2>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Buat Ujian</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@forelse($exams as $exam)
<div class="bg-white rounded-2xl border border-slate-100 p-5 hover:border-accent-100 hover:shadow-sm transition">
    <div class="flex items-start justify-between mb-2">
        <span class="px-2.5 py-1 text-xs rounded-lg bg-rose-50 text-rose-600 font-bold">{{ $exam->code }}</span>
        <span class="px-2 py-1 text-xs rounded-full {{ $exam->status=='published'?'bg-sky-50 text-sky-600':($exam->status=='ongoing'?'bg-emerald-50 text-emerald-600':'bg-slate-100 text-slate-500') }}">{{ $exam->status }}</span>
    </div>
    <h3 class="font-semibold text-slate-800 mb-1">{{ $exam->title }}</h3>
    <div class="space-y-1 text-xs text-slate-400 mb-3">
        <div class="flex items-center gap-1.5"><i data-lucide="book-open" class="w-3 h-3"></i> {{ $exam->subject->name ?? '-' }}</div>
        <div class="flex items-center gap-1.5"><i data-lucide="school" class="w-3 h-3"></i> {{ is_array($exam->class_ids) ? count($exam->class_ids) : 0 }} kelas</div>
        <div class="flex items-center gap-1.5"><i data-lucide="clock" class="w-3 h-3"></i> {{ $exam->duration ?? 60 }} menit | {{ \Carbon\Carbon::parse($exam->start_time)->format('d/m/Y H:i') }}</div>
    </div>
    <div class="flex gap-2">
        <button onclick="openQuestModal('{{ $exam->id }}')" class="flex-1 px-3 py-1.5 rounded-lg bg-slate-50 text-xs font-medium hover:bg-accent-50 hover:text-accent text-center">{{ $exam->examQuestions->count() ?? 0 }} soal</button>
        <button onclick="openEditModal('{{ $exam->id }}')" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 text-xs font-medium" title="Edit"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
        <form method="POST" action="{{ route('exam.list.delete', $exam->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus ujian {{ $exam->title }}? Semua sesi & hasil terkait juga akan dihapus.', 'Hapus Ujian', 'Ya, Hapus', () => this.submit());" class="inline">@csrf @method('DELETE')<button class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-medium" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button></form>
    </div>
</div>
@empty
<div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-100"><i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i><p class="text-slate-400">Belum ada ujian.</p></div>
@endforelse
</div>
<div class="mt-4">{{ $exams->links() }}</div>

{{-- MODAL BUAT UJIAN --}}
<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Buat Ujian</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('exam.list.store') }}" class="p-5 space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Ujian</label><select name="type" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="uh">Ulangan Harian</option><option value="sts">STS</option><option value="sas">SAS</option><option value="asaj">ASAJ</option><option value="tryout">Tryout</option></select></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode (opsional)</label><input name="code" placeholder="UH-1" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul</label><input name="title" placeholder="Ulangan Harian 1" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mapel</label><select name="subject_id" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label><div class="grid grid-cols-2 gap-1 max-h-28 overflow-y-auto border rounded-xl p-2">@foreach($classes as $c)<label class="flex items-center gap-1.5 text-xs cursor-pointer hover:bg-slate-50 rounded p-1"><input type="checkbox" name="class_ids[]" value="{{ $c->id }}" class="rounded accent-accent">{{ $c->code }}</label>@endforeach</div></div>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label><select name="semester_id" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($semesters as $sm)<option value="{{ $sm->id }}" {{ $sm->is_active?'selected':'' }}>{{ $sm->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mulai</label><input name="start_time" type="datetime-local" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Selesai</label><input name="end_time" type="datetime-local" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Durasi (menit)</label><input name="duration" type="number" min="1" value="60" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>

{{-- MODAL TAMBAH SOAL KE UJIAN --}}
<div class="modal-overlay" id="questMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambahkan Soal ke Ujian</h3><button onclick="closeQuest()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form id="questForm" method="POST" action="" class="p-5 space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih Soal</label>
    <div class="max-h-60 overflow-y-auto space-y-2 border rounded-xl p-3">
        @foreach($banks as $bank)
        <div class="mb-2"><span class="text-[11px] font-bold text-slate-400 uppercase">{{ $bank->name }}</span>
            @foreach($bank->questions as $q)
            <label class="flex items-center gap-2 py-1.5 px-2 rounded-lg hover:bg-slate-50 cursor-pointer text-xs">
                <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="rounded accent-accent">
                <span class="line-clamp-1 text-slate-600">{{ $q->content }}</span>
            </label>
            @endforeach
        </div>
        @endforeach
    </div></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeQuest()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
{{-- MODAL EDIT UJIAN --}}
<div class="modal-overlay" id="editMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Edit Ujian</h3><button onclick="closeEdit()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form id="editForm" method="POST" action="" class="p-5 space-y-4">
    @csrf @method('PUT')
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Ujian</label><select name="type" id="editType" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="uh">Ulangan Harian</option><option value="sts">STS</option><option value="sas">SAS</option><option value="asaj">ASAJ</option><option value="tryout">Tryout</option></select></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode (opsional)</label><input name="code" id="editCode" placeholder="UH-1" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul</label><input name="title" id="editTitle" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mapel</label><select name="subject_id" id="editSubject" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label><div class="grid grid-cols-2 gap-1 max-h-28 overflow-y-auto border rounded-xl p-2" id="editClasses">@foreach($classes as $c)<label class="flex items-center gap-1.5 text-xs cursor-pointer hover:bg-slate-50 rounded p-1"><input type="checkbox" name="class_ids[]" value="{{ $c->id }}" class="rounded accent-accent">{{ $c->code }}</label>@endforeach</div></div>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label><select name="semester_id" id="editSemester" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($semesters as $sm)<option value="{{ $sm->id }}">{{ $sm->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mulai</label><input name="start_time" id="editStart" type="datetime-local" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Selesai</label><input name="end_time" id="editEnd" type="datetime-local" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Durasi (menit)</label><input name="duration" id="editDuration" type="number" min="1" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Status</label><select name="status" id="editStatus" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="draft">Draft</option><option value="published">Published</option><option value="ongoing">Ongoing</option><option value="finished">Finished</option></select></div>
        <div class="space-y-2">
            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="random_questions" id="editRandomQ" value="1" class="rounded accent-accent"> Acak Soal</label>
            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="random_answers" id="editRandomA" value="1" class="rounded accent-accent"> Acak Jawaban</label>
            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="show_result" id="editShowResult" value="1" class="rounded accent-accent"> Tampilkan Hasil</label>
        </div>
    </div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeEdit()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
// Data ujian untuk edit modal
const examData = @json($examData);

function openModal(){document.getElementById('mod').classList.add('show')}function closeMod(){document.getElementById('mod').classList.remove('show')}
function openQuestModal(examId){document.getElementById('questForm').action='{{ url("/backend/exam/list") }}/'+examId+'/questions';document.getElementById('questMod').classList.add('show')}
function closeQuest(){document.getElementById('questMod').classList.remove('show')}

function openEditModal(examId) {
    const d = examData[examId];
    if (!d) return;
    document.getElementById('editForm').action = '{{ url("/backend/exam/list") }}/' + examId;
    document.getElementById('editCode').value = d.code || '';
    document.getElementById('editTitle').value = d.title;
    document.getElementById('editType').value = d.type;
    document.getElementById('editSubject').value = d.subject_id;
    document.getElementById('editSemester').value = d.semester_id;
    document.getElementById('editStart').value = d.start_time;
    document.getElementById('editEnd').value = d.end_time;
    document.getElementById('editDuration').value = d.duration;
    document.getElementById('editStatus').value = d.status;
    document.getElementById('editRandomQ').checked = d.random_questions;
    document.getElementById('editRandomA').checked = d.random_answers;
    document.getElementById('editShowResult').checked = d.show_result;
    // Set class checkboxes
    var classIds = d.class_ids || [];
    document.querySelectorAll('#editClasses input[name="class_ids[]"]').forEach(function(cb) {
        cb.checked = classIds.includes(cb.value);
    });
    document.getElementById('editMod').classList.add('show');
}
function closeEdit(){document.getElementById('editMod').classList.remove('show')}

document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show')}))
</script>
@endpush
