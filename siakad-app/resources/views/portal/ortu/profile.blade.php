@extends('portal.layout')
@section('title', 'Profil — Portal Orang Tua')
@section('sidebar-nav')
<a href="/portal/ortu/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/ortu/children" class="sidebar-link"><i data-lucide="users" class="w-5 h-5"></i> Anak Saya</a>
<a href="/portal/ortu/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/ortu/bills" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/ortu/profile" class="sidebar-link active"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Profil Saya</h1></div>
<div class="max-w-2xl">
    <div class="card p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-2xl" id="profAvatar">?</div>
            <div><h2 class="text-xl font-bold text-slate-800" id="profName">Loading...</h2><p class="text-sm text-slate-400">Orang Tua / Wali</p></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="text-xs text-slate-400 font-medium">Nama</label><p class="text-sm font-semibold text-slate-700" id="profFullName">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Hubungan</label><p class="text-sm font-semibold text-slate-700" id="profRelation">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Email</label><p class="text-sm font-semibold text-slate-700" id="profEmail">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Phone</label><p class="text-sm font-semibold text-slate-700" id="profPhone">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Pekerjaan</label><p class="text-sm font-semibold text-slate-700" id="profJob">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Alamat</label><p class="text-sm font-semibold text-slate-700" id="profAddress">-</p></div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/guardian/dashboard`);
        const g = data.guardian || {};
        const u = currentUser || {};
        document.getElementById('profAvatar').textContent = (g.nama || u.name || '?').charAt(0).toUpperCase();
        document.getElementById('profName').textContent = g.nama || u.name || '-';
        document.getElementById('profFullName').textContent = g.nama || u.name || '-';
        document.getElementById('profRelation').textContent = g.hubungan || '-';
        document.getElementById('profEmail').textContent = u.email || '-';
        document.getElementById('profPhone').textContent = g.phone || '-';
        document.getElementById('profJob').textContent = g.pekerjaan || '-';
        document.getElementById('profAddress').textContent = g.alamat || '-';
    } catch (e) { document.getElementById('profName').textContent = 'Gagal memuat profil'; }
});
</script>
@endsection
