@extends('portal.layout')
@section('title', 'Dashboard — Portal Orang Tua')
@section('sidebar-nav')
<a href="/portal/ortu/dashboard" class="sidebar-link active"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/ortu/children" class="sidebar-link"><i data-lucide="users" class="w-5 h-5"></i> Anak Saya</a>
<a href="/portal/ortu/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/ortu/bills" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/ortu/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Orang Tua</h1>
    <p class="text-slate-400 text-sm mt-1">Ringkasan akademik anak Anda</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card p-5"><div class="text-xs text-slate-400 font-medium mb-1">Jumlah Anak</div><div class="text-3xl font-bold text-slate-800" id="sumAnak">--</div></div>
    <div class="card p-5"><div class="text-xs text-slate-400 font-medium mb-1">Total Tunggakan</div><div class="text-3xl font-bold text-slate-800" id="sumTunggakan">Rp 0</div></div>
    <div class="card p-5"><div class="text-xs text-slate-400 font-medium mb-1">Ujian Mendatang</div><div class="text-3xl font-bold text-slate-800" id="sumUjian">0</div></div>
</div>

<div id="childrenContainer" class="space-y-4">
    <div class="card p-5 animate-pulse"><div class="h-4 bg-slate-100 rounded w-1/3 mb-3"></div><div class="h-4 bg-slate-100 rounded w-1/2"></div></div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/guardian/dashboard`);
        const children = data.data || [];
        const summary = data.summary || {};

        document.getElementById('sumAnak').textContent = summary.jumlah_anak || children.length;
        document.getElementById('sumTunggakan').textContent = formatRupiah(summary.total_tunggakan || 0);

        let totalExams = 0;
        const el = document.getElementById('childrenContainer');

        if (children.length) {
            el.innerHTML = children.map(c => {
                totalExams += (c.ujian_mendatang || 0);
                const attColor = (c.persentase_hadir || 0) >= 90 ? 'green' : (c.persentase_hadir >= 75 ? 'amber' : 'red');
                return `<div class="card p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-lg">${(c.nama || '?').charAt(0)}</div>
                            <div>
                                <h3 class="font-semibold text-slate-800">${c.nama}</h3>
                                <p class="text-xs text-slate-400">${c.nis} · ${c.kelas} · ${c.tingkat}</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-slate-700">${(c.nilai_rata_rata || 0).toFixed(1)}</div><div class="text-xs text-slate-400">Rata-rata Nilai</div></div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-${attColor}-600">${c.persentase_hadir || 0}%</div><div class="text-xs text-slate-400">Kehadiran</div></div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold ${(c.tunggakan || 0) > 0 ? 'text-red-600' : 'text-green-600'}">${formatRupiah(c.tunggakan || 0)}</div><div class="text-xs text-slate-400">Tunggakan</div></div>
                    </div>
                </div>`;
            }).join('');
            document.getElementById('sumUjian').textContent = totalExams;
        } else {
            el.innerHTML = '<div class="card p-8 text-center"><p class="text-slate-500">Belum ada data anak terdaftar.</p></div>';
        }
    } catch (e) {
        document.getElementById('childrenContainer').innerHTML = '<div class="card p-5 text-center text-red-500">Gagal memuat data.</div>';
    }
});
</script>
@endsection
