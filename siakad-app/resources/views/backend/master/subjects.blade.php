@extends('layouts.backend')

@section('title', 'Mata Pelajaran')
@section('page_title', 'Mata Pelajaran')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border-accent-100 text-accent text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
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
    <form method="GET"><input name="search" value="{{ request('search') }}" placeholder="Cari mapel..." class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200 w-52"></form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Mapel</button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left">
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kode</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Mapel</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kategori</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-50">
@forelse($subjects as $s)
<tr class="hover:bg-slate-50/50">
    <td class="px-5 py-3.5"><span class="font-mono text-sm font-semibold text-accent">{{ $s->code }}</span></td>
    <td class="px-5 py-3.5 font-medium text-slate-800">{{ $s->name }}</td>
    <td class="px-5 py-3.5"><span class="px-2 py-1 text-xs rounded-full bg-slate-100">{{ $s->kategori ?? 'Umum' }}</span></td>
    <td class="px-5 py-3.5"><span class="px-2 py-1 text-xs rounded-full {{ $s->is_active?'bg-accent-50 text-accent':'bg-red-50 text-red-600' }}">{{ $s->is_active?'Aktif':'Nonaktif' }}</span></td>
    <td class="px-5 py-3.5"><div class="flex gap-1">
        <button data-edit-id="{{ $s->id }}" data-edit-code="{{ $s->code }}" data-edit-name="{{ addslashes($s->name) }}" data-edit-kategori="{{ $s->kategori ?? '' }}" data-edit-active="{{ $s->is_active }}" class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent js-edit-btn" title="Edit"><i data-lucide="pencil" class="w-4 h-4"></i></button>
        <form method="POST" action="{{ route('master.subjects.delete',$s->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus mapel ini?', 'Hapus Mapel', 'Ya, Hapus', () => this.submit());" class="inline">@csrf<button class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
    </div></td>
</tr>
@empty
<tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Belum ada mata pelajaran.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <span>Tampilkan</span>
        <select onchange="changePerPage(this.value)"
            class="form-select text-sm w-20 py-1.5 rounded-lg border-slate-200">
            <option value="10"  {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="20"  {{ $perPage == 20 ? 'selected' : '' }}>20</option>
            <option value="50"  {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span>data</span>
    </div>
    {{ $subjects->links() }}
</div>

{{-- MODAL --}}
<div class="modal-overlay" id="mod"><div class="modal-box">
<div class="p-5 border-b flex justify-between"><h3 class="font-semibold text-slate-800" id="mTitle">Tambah Mapel</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form id="mForm" method="POST" action="{{ route('master.subjects.store') }}" class="p-5 space-y-4">
    @csrf<input type="hidden" name="_method" id="mMethod" value="POST">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" id="mCode" placeholder="BIND" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Mapel</label><input name="name" id="mName" placeholder="Bahasa Indonesia" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kategori</label><select name="kategori" id="mGroup" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"><option value="umum">Umum</option><option value="kejuruan">Kejuruan</option><option value="muatan_lokal">Muatan Lokal</option></select></div>
    </div>
    <div class="flex items-center gap-2"><input type="checkbox" name="is_active" id="mActive" value="1" checked class="rounded"><label for="mActive" class="text-sm text-slate-600">Aktif</label></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Batal</button></div>
</form>
</div></div>
@endsection

@push('scripts')
<script>
function openModal(){reset();document.getElementById('mTitle').textContent='Tambah Mapel';document.getElementById('mForm').action='{{ route("master.subjects.store") }}';document.getElementById('mMethod').value='POST';document.getElementById('mod').classList.add('show')}
function editSub(id,code,name,group,active){reset();document.getElementById('mTitle').textContent='Edit Mapel';document.getElementById('mForm').action='{{ url("/backend/master/subjects") }}/'+id;document.getElementById('mMethod').value='PUT';document.getElementById('mCode').value=code;document.getElementById('mName').value=name;document.getElementById('mGroup').value=group;document.getElementById('mActive').checked=active=='1';document.getElementById('mod').classList.add('show')}
function reset(){['mCode','mName','mGroup'].forEach(f=>document.getElementById(f).value='');document.getElementById('mActive').checked=true}
function closeMod(){document.getElementById('mod').classList.remove('show')}
// Event delegation
document.addEventListener('DOMContentLoaded',function(){
    document.querySelector('table').addEventListener('click',function(e){
        var btn = e.target.closest('.js-edit-btn');
        if (!btn) return;
        editSub(btn.dataset.editId, btn.dataset.editCode, btn.dataset.editName, btn.dataset.editKategori, btn.dataset.editActive);
    });
    var mod = document.getElementById('mod');
    if (mod) mod.addEventListener('click',function(e){if(e.target===this)closeMod();});
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
