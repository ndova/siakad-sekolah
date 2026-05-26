@extends('portal.layout')
@section('title', 'Profil — Portal Siswa')
@section('sidebar-nav')
<a href="/portal/siswa/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/siswa/grades" class="sidebar-link"><i data-lucide="bar-chart-3" class="w-5 h-5"></i> Nilai & Rapor</a>
<a href="/portal/siswa/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/siswa/exams" class="sidebar-link"><i data-lucide="file-text" class="w-5 h-5"></i> Ujian</a>
<a href="/portal/siswa/payments" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/siswa/profile" class="sidebar-link active"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Profil</h1>
</div>

<div class="max-w-2xl">
    <div class="card p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-2xl" id="profileAvatar">?</div>
            <div>
                <h2 class="text-xl font-bold text-slate-800" id="profileName">Loading...</h2>
                <p class="text-sm text-slate-400" id="profileRole">Siswa</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="text-xs text-slate-400 font-medium">NIS</label><p class="text-sm font-semibold text-slate-700" id="profileNis">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">NISN</label><p class="text-sm font-semibold text-slate-700" id="profileNisn">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Kelas</label><p class="text-sm font-semibold text-slate-700" id="profileClass">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Wali Kelas</label><p class="text-sm font-semibold text-slate-700" id="profileWaliKelas">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Tempat, Tanggal Lahir</label><p class="text-sm font-semibold text-slate-700" id="profileTtl">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Agama</label><p class="text-sm font-semibold text-slate-700" id="profileAgama">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Alamat</label><p class="text-sm font-semibold text-slate-700" id="profileAlamat">-</p></div>
            <div><label class="text-xs text-slate-400 font-medium">Email</label><p class="text-sm font-semibold text-slate-700" id="profileEmail">-</p></div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/student/dashboard`);
        const s = data.data?.student || data.student || {};

        document.getElementById('profileAvatar').textContent = (s.nama || '?').charAt(0).toUpperCase();
        document.getElementById('profileName').textContent = s.nama || '-';
        document.getElementById('profileRole').textContent = 'Siswa · ' + (s.tingkat || '-');
        document.getElementById('profileNis').textContent = s.nis || '-';
        document.getElementById('profileNisn').textContent = s.nisn || '-';
        document.getElementById('profileClass').textContent = s.kelas || '-';
        document.getElementById('profileWaliKelas').textContent = s.wali_kelas || '-';
        document.getElementById('profileTtl').textContent = [s.tempat_lahir, s.tanggal_lahir].filter(Boolean).join(', ') || '-';
        document.getElementById('profileAgama').textContent = s.agama || '-';
        document.getElementById('profileAlamat').textContent = s.alamat || '-';
        document.getElementById('profileEmail').textContent = s.email || currentUser?.email || '-';
    } catch (e) { document.getElementById('profileName').textContent = 'Gagal memuat profil'; }
});
</script>
@endsection
