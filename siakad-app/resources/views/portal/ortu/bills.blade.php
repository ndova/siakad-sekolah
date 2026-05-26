@extends('portal.layout')
@section('title', 'Pembayaran — Portal Orang Tua')
@section('sidebar-nav')
<a href="/portal/ortu/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/ortu/children" class="sidebar-link"><i data-lucide="users" class="w-5 h-5"></i> Anak Saya</a>
<a href="/portal/ortu/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/ortu/bills" class="sidebar-link active"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/ortu/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Pembayaran</h1>
    <p class="text-slate-400 text-sm mt-1">Tagihan dan riwayat pembayaran</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-700 text-sm">Tagihan Aktif</div>
        <div id="billsList" class="divide-y divide-slate-50">
            <div class="px-5 py-8 text-center text-slate-400">Memuat...</div>
        </div>
    </div>
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-700 text-sm">Riwayat Pembayaran</div>
        <div id="historyList" class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
            <div class="px-5 py-8 text-center text-slate-400">Memuat...</div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div id="payModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h3 class="font-bold text-lg text-slate-800 mb-4">Form Pembayaran</h3>
        <div class="space-y-3 mb-4">
            <div><label class="text-xs text-slate-400">Tagihan</label><p class="text-sm font-semibold text-slate-700" id="payInvoice">-</p></div>
            <div><label class="text-xs text-slate-400">Sisa Tagihan</label><p class="text-sm font-bold text-red-600" id="payRemaining">Rp 0</p></div>
            <div><label class="text-xs text-slate-400">Jumlah Bayar</label><input type="number" id="payAmount" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none text-sm" placeholder="Masukkan jumlah"></div>
        </div>
        <div class="flex gap-3">
            <button onclick="closePayModal()" class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
            <button onclick="submitPayment()" class="flex-1 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700">Bayar</button>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
let currentInvoice = null;
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const [billsData, historyData] = await Promise.all([
            apiFetch(`${API_BASE}/guardian/bills`),
            apiFetch(`${API_BASE}/guardian/payments/history`)
        ]);
        const bills = billsData.data || [];
        const history = historyData.data || [];

        const billsEl = document.getElementById('billsList');
        if (bills.length) {
            billsEl.innerHTML = bills.map(inv => {
                const paid = inv.paid_amount || 0; const total = inv.total || 0; const remaining = total - paid;
                const colors = { paid:'green', unpaid:'red', partial:'amber', void:'slate' };
                const labels = { paid:'Lunas', unpaid:'Belum Bayar', partial:'Sebagian', void:'Dibatalkan' };
                const color = colors[inv.status] || 'slate';
                return `<div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-slate-700">${inv.invoice_number || 'INV'} ${inv.student_name ? '— ' + inv.student_name : ''}</span>
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-${color}-100 text-${color}-700">${labels[inv.status] || inv.status}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total: ${formatRupiah(total)} · Sisa: ${formatRupiah(Math.max(0, remaining))}</span>
                        <span class="text-xs text-slate-400">Jatuh tempo: ${inv.due_date || '-'}</span>
                    </div>
                    ${inv.status !== 'paid' && inv.status !== 'void' ? `<button onclick="openPayModal('${inv.id}', '${inv.invoice_number || 'INV'}', ${remaining})" class="mt-2 text-xs font-semibold text-purple-600 hover:text-purple-800">Bayar Sekarang →</button>` : ''}
                </div>`;
            }).join('');
        } else { billsEl.innerHTML = '<div class="px-5 py-8 text-center text-slate-400">Tidak ada tagihan.</div>'; }

        const histEl = document.getElementById('historyList');
        if (history.length) {
            histEl.innerHTML = history.map(p => `<div class="px-5 py-3 flex items-center justify-between"><div><div class="text-sm font-medium text-slate-700">${p.payment_number || 'PAY'}</div><div class="text-xs text-slate-400">${p.student_name || ''} · ${p.payment_date || '-'}</div></div><div class="text-right"><div class="text-sm font-bold">${formatRupiah(p.amount || 0)}</div><span class="inline-flex px-1.5 py-0.5 rounded text-xs font-semibold ${p.status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">${p.status}</span></div></div>`).join('');
        } else { histEl.innerHTML = '<div class="px-5 py-8 text-center text-slate-400">Belum ada riwayat pembayaran.</div>'; }
    } catch (e) {
        document.getElementById('billsList').innerHTML = '<div class="px-5 py-8 text-center text-red-500">Gagal memuat data.</div>';
        document.getElementById('historyList').innerHTML = '<div class="px-5 py-8 text-center text-red-500">Gagal memuat data.</div>';
    }
});

function openPayModal(invoiceId, invoiceNumber, remaining) {
    currentInvoice = invoiceId;
    document.getElementById('payInvoice').textContent = invoiceNumber;
    document.getElementById('payRemaining').textContent = formatRupiah(remaining);
    document.getElementById('payAmount').value = '';
    document.getElementById('payAmount').max = remaining;
    document.getElementById('payModal').classList.remove('hidden');
}

function closePayModal() { document.getElementById('payModal').classList.add('hidden'); currentInvoice = null; }

async function submitPayment() {
    const amount = document.getElementById('payAmount').value;
    if (!amount || amount <= 0) { showToast('Masukkan jumlah pembayaran.', 'error'); return; }
    try {
        await apiFetch(`${API_BASE}/guardian/payments/${currentInvoice}/pay`, {
            method: 'POST',
            body: JSON.stringify({ amount: parseFloat(amount) })
        });
        closePayModal();
        showToast('Pembayaran berhasil dikirim! Menunggu verifikasi bendahara.', 'success');
        setTimeout(() => location.reload(), 1500);
    } catch (e) {
        showToast(e.message || 'Pembayaran gagal.', 'error');
    }
}
</script>
@endsection
