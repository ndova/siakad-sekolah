@extends('layouts.backend')

@section('title', 'Jenis Biaya')
@section('page_title', 'Jenis Biaya')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

<div class="flex justify-between items-center mb-5">
    <form method="GET" class="flex gap-2">
        <select name="category" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"><option value="">Semua</option><option value="rutin" {{ request('category')=='rutin'?'selected':'' }}>Rutin</option><option value="tidak_rutin" {{ request('category')=='tidak_rutin'?'selected':'' }}>Tidak Rutin</option></select>
    </form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah</button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 text-left">
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kode</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kategori</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Periode</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Nominal</th>
    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
</tr></thead>
<tbody class="divide-y divide-slate-50">
@forelse($feeTypes as $ft)
<tr class="hover:bg-slate-50/30">
    <td class="px-5 py-3.5 font-mono text-sm font-semibold text-amber-600">{{ $ft->code }}</td>
    <td class="px-5 py-3.5 font-medium text-slate-800">{{ $ft->name }}</td>
    <td class="px-5 py-3.5"><span class="px-2 py-1 text-xs rounded-full {{ $ft->category=='rutin'?'bg-sky-50 text-sky-600':'bg-violet-50 text-violet-600' }}">{{ $ft->category=='rutin'?'Rutin':'Tidak Rutin' }}</span></td>
    <td class="px-5 py-3.5"><span class="px-2 py-1 text-xs rounded bg-slate-100">{{ $ft->billing_period=='monthly'?'Bulanan':($ft->billing_period=='semester'?'Semester':'Tahunan') }}</span></td>
    <td class="px-5 py-3.5 text-right font-mono font-semibold">Rp{{ number_format($ft->nominal,0,',','.') }}</td>
    <td class="px-5 py-3.5"><span class="px-2 py-1 text-xs rounded-full {{ $ft->is_active?'bg-emerald-50 text-emerald-600':'bg-red-50 text-red-600' }}">{{ $ft->is_active?'Aktif':'Nonaktif' }}</span></td>
</tr>
@empty
<tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada jenis biaya.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $feeTypes->links() }}</div>

<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Jenis Biaya</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('finance.fee-types.store') }}" class="p-5 space-y-4">
    @csrf
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" placeholder="SPP" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama</label><input name="name" placeholder="SPP Bulanan" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kategori</label><select name="category" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="rutin">Rutin</option><option value="tidak_rutin">Tidak Rutin</option></select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Periode</label><select name="billing_period" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="monthly">Bulanan</option><option value="semester">Semester</option><option value="yearly">Tahunan</option></select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Nominal</label><input name="nominal" type="number" min="0" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
function openModal(){document.getElementById('mod').classList.add('show')}function closeMod(){document.getElementById('mod').classList.remove('show')}
document.getElementById('mod').addEventListener('click',function(e){if(e.target===this)closeMod()})
</script>
@endpush
