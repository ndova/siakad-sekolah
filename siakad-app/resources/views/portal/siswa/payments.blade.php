@extends('portal.layout')
@section('title', 'Pembayaran — Portal Siswa')
@section('sidebar-nav')
<a href="/portal/siswa/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/siswa/grades" class="sidebar-link"><i data-lucide="bar-chart-3" class="w-5 h-5"></i> Nilai & Rapor</a>
<a href="/portal/siswa/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/siswa/exams" class="sidebar-link"><i data-lucide="file-text" class="w-5 h-5"></i> Ujian</a>
<a href="/portal/siswa/payments" class="sidebar-link active"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/siswa/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Pembayaran</h1>
    <p class="text-slate-400 text-sm mt-1">Tagihan dan riwayat pembayaran Anda</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-700 text-sm">Tagihan</div>
        <div class="divide-y divide-slate-50" id="invoiceList">
            <div class="px-5 py-8 text-center text-slate-400 text-sm">Memuat...</div>
        </div>
    </div>
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-700 text-sm">Riwayat Pembayaran</div>
        <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto" id="paymentHistory">
            <div class="px-5 py-8 text-center text-slate-400 text-sm">Memuat...</div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/student/payments`);
        const invoices = data.invoices || data.data?.invoices || [];
        const payments = data.payments || data.data?.payments || [];

        const invEl = document.getElementById('invoiceList');
        if (invoices.length) {
            invEl.innerHTML = invoices.map(inv => {
                const paid = inv.paid_amount || 0;
                const total = inv.total || 0;
                const remaining = total - paid;
                const statusColors = { paid: 'green', unpaid: 'red', partial: 'amber', void: 'slate' };
                const color = statusColors[inv.status] || 'slate';
                const statusLabel = { paid: 'Lunas', unpaid: 'Belum Bayar', partial: 'Sebagian', void: 'Dibatalkan' };
                return `<div class="px-5 py-3"><div class="flex items-center justify-between mb-2"><span class="text-sm font-medium text-slate-700">${inv.invoice_number || 'INV'}</span><span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-${color}-100 text-${color}-700">${statusLabel[inv.status] || inv.status}</span></div><div class="flex items-center justify-between text-xs text-slate-500"><span>Total: ${formatRupiah(total)}</span><span class="text-red-500 font-medium">Sisa: ${formatRupiah(Math.max(0, remaining))}</span></div><div class="text-xs text-slate-400 mt-1">Jatuh tempo: ${inv.due_date || '-'}</div></div>`;
            }).join('');
        } else { invEl.innerHTML = '<div class="px-5 py-8 text-center text-slate-400 text-sm">Tidak ada tagihan.</div>'; }

        const payEl = document.getElementById('paymentHistory');
        if (payments.length) {
            payEl.innerHTML = payments.map(p => `<div class="px-5 py-3 flex items-center justify-between"><div><div class="text-sm font-medium text-slate-700">${p.payment_number || 'PAY'}</div><div class="text-xs text-slate-400">${p.payment_date || '-'} · ${p.payment_channel || '-'}</div></div><div class="text-right"><div class="text-sm font-bold text-slate-700">${formatRupiah(p.amount || 0)}</div><span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold ${p.status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">${p.status === 'verified' ? 'Diverifikasi' : p.status}</span></div></div>`).join('');
        } else { payEl.innerHTML = '<div class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada pembayaran.</div>'; }
    } catch (e) {
        document.getElementById('invoiceList').innerHTML = '<div class="px-5 py-8 text-center text-red-500 text-sm">Gagal memuat data.</div>';
        document.getElementById('paymentHistory').innerHTML = '<div class="px-5 py-8 text-center text-red-500 text-sm">Gagal memuat data.</div>';
    }
});
</script>
@endsection
