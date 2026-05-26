@extends('layouts.backend')

@section('title', 'Rombongan Belajar')
@section('page_title', 'Rombongan Belajar')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border border-accent-100 text-accent text-sm flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">
    <div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal menyimpan:</span></div>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="flex justify-between items-center mb-5">
    <h2 class="text-sm text-slate-500">Total: {{ $classes->total() }} rombel</h2>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kelas
    </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($classes as $kelas)
    <div class="bg-white rounded-2xl border border-slate-100 p-5 hover:shadow-sm hover:border-accent-100 transition">
        <div class="flex items-start justify-between mb-2">
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-accent-50 text-accent">{{ $kelas->code }}</span>
            <span class="px-2 py-1 text-xs rounded-full {{ $kelas->is_active?'bg-accent-50 text-accent':'bg-red-50 text-red-600' }}">{{ $kelas->is_active?'Aktif':'Nonaktif' }}</span>
        </div>
        <h3 class="font-semibold text-slate-800 mb-3">{{ $kelas->name }}</h3>
        <div class="space-y-1.5 text-xs text-slate-400 mb-4">
            <div class="flex items-center gap-1.5"><i data-lucide="users" class="w-3 h-3"></i> {{ $kelas->students_count ?? 0 }} siswa</div>
            @if($kelas->waliKelas)<div class="flex items-center gap-1.5"><i data-lucide="user-check" class="w-3 h-3"></i> {{ $kelas->waliKelas->name }}</div>@endif
        </div>
        <div class="flex gap-2">
            <button data-edit-id="{{ $kelas->id }}" data-edit-code="{{ $kelas->code }}" data-edit-name="{{ addslashes($kelas->code) }}" data-edit-year="{{ $kelas->academic_year_id }}" data-edit-major="{{ $kelas->major_id ?? '' }}" data-edit-ht="{{ $kelas->wali_kelas_id ?? '' }}" data-edit-active="{{ $kelas->is_active }}" class="flex-1 py-1.5 rounded-lg bg-slate-50 text-xs font-medium hover:bg-accent-50 hover:text-accent text-center js-edit-btn">Edit</button>
            <form method="POST" action="{{ route('master.classes.delete',$kelas->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus kelas ini?', 'Hapus Kelas', 'Ya, Hapus', () => this.submit());" class="flex-1">
                @csrf<button class="w-full py-1.5 rounded-lg bg-slate-50 text-xs font-medium hover:bg-red-50 hover:text-red-600">Hapus</button></form>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16 text-slate-400">
        <i data-lucide="school" class="w-12 h-12 mx-auto mb-3 opacity-30"></i><p>Belum ada data kelas.</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $classes->links() }}</div>

{{-- MODAL --}}
<div class="modal-overlay" id="mod">
<div class="modal-box">
<div class="p-5 border-b flex items-center justify-between">
    <h3 class="font-semibold text-slate-800" id="mTitle">Tambah Kelas</h3>
    <button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
</div>
<form id="mForm" method="POST" action="{{ route('master.classes.store') }}" class="p-5 space-y-4">
    @csrf<input type="hidden" name="_method" id="mMethod" value="POST">
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" id="mCode" placeholder="7A" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun Ajaran</label><select name="academic_year_id" id="mYear" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"><option value="">Pilih</option>@foreach($academicYears as $y)<option value="{{ $y->id }}">{{ $y->code }}</option>@endforeach</select></div>
    </div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Kelas</label><input name="name" id="mName" placeholder="Kelas 7A" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Jurusan</label><select name="major_id" id="mMajor" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"><option value="">Umum</option>@foreach($majors as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Wali Kelas</label><select name="wali_kelas_id" id="mHt" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"><option value="">-</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
    </div>
    <div class="flex items-center gap-2"><input type="checkbox" name="is_active" id="mActive" value="1" checked class="rounded"><label for="mActive" class="text-sm text-slate-600">Aktif</label></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Batal</button></div>
</form>
</div></div>
@endsection

@push('scripts')
<script>
function openModal(){reset();document.getElementById('mTitle').textContent='Tambah Kelas';document.getElementById('mForm').action='{{ route("master.classes.store") }}';document.getElementById('mMethod').value='POST';document.getElementById('mod').classList.add('show')}
function editClass(id,code,name,year,major,ht,active){reset();document.getElementById('mTitle').textContent='Edit Kelas';document.getElementById('mForm').action='{{ url("/backend/master/classes") }}/'+id;document.getElementById('mMethod').value='PUT';document.getElementById('mCode').value=code;document.getElementById('mName').value=name;document.getElementById('mYear').value=year;document.getElementById('mMajor').value=major;document.getElementById('mHt').value=ht;document.getElementById('mActive').checked=active=='1';document.getElementById('mod').classList.add('show')}
function reset(){['mCode','mName','mYear','mMajor','mHt'].forEach(f=>document.getElementById(f).value='');document.getElementById('mActive').checked=true}
function closeMod(){document.getElementById('mod').classList.remove('show')}
// Event delegation
document.addEventListener('DOMContentLoaded',function(){
    document.querySelector('.grid').addEventListener('click',function(e){
        var btn = e.target.closest('.js-edit-btn');
        if (!btn) return;
        editClass(btn.dataset.editId, btn.dataset.editCode, btn.dataset.editName, btn.dataset.editYear, btn.dataset.editMajor, btn.dataset.editHt, btn.dataset.editActive);
    });
    var mod = document.getElementById('mod');
    if (mod) mod.addEventListener('click',function(e){if(e.target===this)closeMod();});
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
