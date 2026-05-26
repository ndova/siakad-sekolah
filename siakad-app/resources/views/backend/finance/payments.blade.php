@extends('layouts.backend')

@section('title', 'Pembayaran')
@section('page_title', 'Pembayaran')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

@if($pendingCount)
<div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-between">
    <div class="flex items-center gap-2 text-amber-700 text-sm"><i data-lucide="alert-triangle" class="w-4 h-4"></i> {{ $pendingCount }} pembayaran menunggu verifikasi</div>
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2">
        <select name="status" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"><option value="">Semua</option><option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option><option value="verified" {{ request('status')=='verified'?'selected':'' }}>Verified</option><option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Ditolak</option></select>
        <input name="search" value="{{ request('search') }}" placeholder="Cari..." class="px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200 w-40">
    </form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Catat Pembayaran</button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 text-left">
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">No. Pembayaran</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Siswa</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tagihan</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Jumlah</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Metode</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase w-32">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-50">
@forelse($payments as $p)
<tr class="hover:bg-slate-50/30">
    <td class="px-4 py-3 font-mono text-xs">{{ $p->payment_number }}</td>
    <td class="px-4 py-3 font-medium text-slate-800">{{ $p->student->nama_lengkap ?? '-' }}</td>
    <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ $p->invoice->invoice_number ?? '-' }}</td>
    <td class="px-4 py-3 text-right font-mono font-semibold">Rp{{ number_format($p->amount,0,',','.') }}</td>
    <td class="px-4 py-3 text-xs">{{ $p->paymentMethod->name ?? '-' }}</td>
    <td class="px-4 py-3">
        <span class="px-2 py-1 text-xs rounded-full {{ $p->status=='verified'?'bg-emerald-50 text-emerald-600':($p->status=='pending'?'bg-amber-50 text-amber-600':'bg-red-50 text-red-600') }}">{{ $p->status }}</span>
    </td>
    <td class="px-4 py-3 text-xs text-slate-400">{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}</td>
    <td class="px-4 py-3">
        @if($p->status=='pending')
        <div class="flex gap-1">
            <form method="POST" action="{{ route('finance.payments.verify',$p->id) }}" class="inline">@csrf<input type="hidden" name="action" value="verify"><button class="px-2 py-1 rounded text-[10px] font-medium bg-emerald-50 text-emerald-600 hover:bg-emerald-100">Verifikasi</button></form>
            <form method="POST" action="{{ route('finance.payments.verify',$p->id) }}" class="inline">@csrf<input type="hidden" name="action" value="reject"><button class="px-2 py-1 rounded text-[10px] font-medium bg-red-50 text-red-600 hover:bg-red-100">Tolak</button></form>
        </div>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">Belum ada pembayaran.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-4">{{ $payments->links() }}</div>

{{-- MODAL --}}
<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Catat Pembayaran</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('finance.payments.store') }}" class="p-5 space-y-4">
    @csrf
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tagihan</label><select name="invoice_id" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($invoices as $inv)<option value="{{ $inv->id }}">{{ $inv->invoice_number }} — {{ $inv->student->nama_lengkap ?? '' }} (Rp{{ number_format($inv->total - $inv->payments->where('status','verified')->sum('amount'),0,',','.') }} sisa)</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Jumlah</label><input name="amount" type="number" min="0" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Metode</label><select name="payment_method_id" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">@foreach($methods as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal</label><input name="payment_date" type="date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div><div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Dibayar Oleh</label><input name="paid_by" placeholder="Nama" class="w-full px-3 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></div></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Catatan</label><textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
function openModal(){document.getElementById('mod').classList.add('show')}function closeMod(){document.getElementById('mod').classList.remove('show')}
document.getElementById('mod').addEventListener('click',function(e){if(e.target===this)closeMod()})
</script>
@endpush
