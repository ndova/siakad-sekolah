@extends('layouts.backend')

@section('title', 'Tagihan')
@section('page_title', 'Tagihan')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:550px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-2xl p-4 border border-slate-100"><div class="text-xs text-slate-400 mb-1">Total Tagihan</div><div class="text-xl font-bold text-slate-800">Rp{{ number_format($totalTagihan,0,',','.') }}</div></div>
    <div class="bg-white rounded-2xl p-4 border border-emerald-100"><div class="text-xs text-slate-400 mb-1">Terbayar</div><div class="text-xl font-bold text-emerald-600">Rp{{ number_format($totalTerbayar,0,',','.') }}</div></div>
    <div class="bg-white rounded-2xl p-4 border border-red-100"><div class="text-xs text-slate-400 mb-1">Tunggakan</div><div class="text-xl font-bold text-red-500">Rp{{ number_format($totalTunggakan,0,',','.') }}</div></div>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-wrap">
        <select name="status" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="">Semua Status</option><option value="unpaid" {{ request('status')=='unpaid'?'selected':'' }}>Belum</option><option value="partial" {{ request('status')=='partial'?'selected':'' }}>Sebagian</option><option value="paid" {{ request('status')=='paid'?'selected':'' }}>Lunas</option><option value="overdue" {{ request('status')=='overdue'?'selected':'' }}>Terlambat</option></select>
        <select name="class_id" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="">Semua Kelas</option>@foreach($classes as $k)<option value="{{ $k->id }}" {{ request('class_id')==$k->id?'selected':'' }}>{{ $k->code }}</option>@endforeach</select>
        <input name="search" value="{{ request('search') }}" placeholder="Cari..." class="px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200 w-36">
    </form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="layers" class="w-4 h-4"></i> Generate Tagihan</button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 text-left">
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">No. Invoice</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Siswa</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Kelas</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Jumlah</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Jatuh Tempo</th>
</tr></thead>
<tbody class="divide-y divide-slate-50">
@forelse($invoices as $inv)
@php $paid = $inv->payments->where('status','verified')->sum('amount'); @endphp
<tr class="hover:bg-slate-50/30">
    <td class="px-4 py-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
    <td class="px-4 py-3 font-medium text-slate-800">{{ $inv->student->nama_lengkap ?? '-' }}</td>
    <td class="px-4 py-3 text-xs">{{ $inv->student->class->code ?? '-' }}</td>
    <td class="px-4 py-3 text-right font-mono font-semibold">Rp{{ number_format($inv->total,0,',','.') }}</td>
    <td class="px-4 py-3">
        @php $st = $inv->status; @endphp
        <span class="px-2 py-1 text-xs rounded-full {{ $st=='paid'?'bg-emerald-50 text-emerald-600':($st=='partial'?'bg-amber-50 text-amber-600':($st=='overdue'?'bg-red-50 text-red-600':'bg-slate-50 text-slate-500')) }}">
            {{ $st=='paid'?'Lunas':($st=='partial'?'Sebagian':($st=='overdue'?'Terlambat':'Belum')) }}
        </span>
    </td>
    <td class="px-4 py-3 text-xs {{ $inv->due_date < now() && $inv->status!='paid' ? 'text-red-500 font-medium' : 'text-slate-400' }}">{{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">Belum ada tagihan.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $invoices->links() }}</div>

{{-- GENERATE MODAL --}}
<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Generate Tagihan Massal</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('finance.invoices.generate') }}" class="p-5 space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Biaya</label><select name="fee_type_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($feeTypes as $ft)<option value="{{ $ft->id }}">{{ $ft->code }} - {{ $ft->name }} (Rp{{ number_format($ft->nominal,0,',','.') }})</option>@endforeach</select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Target Kelas</label><div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto border rounded-xl p-3">@foreach($classes as $k)<label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-slate-50 rounded p-1.5"><input type="checkbox" name="class_ids[]" value="{{ $k->id }}" class="rounded accent-accent"> {{ $k->code }} - {{ $k->name }}</label>@endforeach</div></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Jatuh Tempo</label><input name="due_date" type="date" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Periode Bulan</label><input name="period_month" type="number" min="1" max="12" value="{{ now()->month }}" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <input type="hidden" name="period_year" value="{{ now()->year }}">
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Generate</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
function openModal(){document.getElementById('mod').classList.add('show')}function closeMod(){document.getElementById('mod').classList.remove('show')}
document.getElementById('mod').addEventListener('click',function(e){if(e.target===this)closeMod()})
</script>
@endpush
