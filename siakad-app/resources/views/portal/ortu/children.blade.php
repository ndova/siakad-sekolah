@extends('portal.layout')
@section('title', 'Anak Saya — Portal Orang Tua')
@section('sidebar-nav')
<a href="/portal/ortu/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/ortu/children" class="sidebar-link active"><i data-lucide="users" class="w-5 h-5"></i> Anak Saya</a>
<a href="/portal/ortu/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/ortu/bills" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/ortu/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Anak Saya</h1>
    <p class="text-slate-400 text-sm mt-1">Detail akademik setiap anak</p>
</div>

<div id="childrenList" class="space-y-6">
    <div class="card p-5 animate-pulse"><div class="h-4 bg-slate-100 rounded w-1/2 mb-3"></div><div class="h-20 bg-slate-100 rounded"></div></div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/guardian/children`);
        const children = data.data || [];

        if (children.length) {
            document.getElementById('childrenList').innerHTML = children.map(c => `
                <div class="card p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xl">${(c.nama || '?').charAt(0)}</div>
                        <div>
                            <h3 class="font-semibold text-slate-800">${c.nama}</h3>
                            <p class="text-xs text-slate-400">${c.nis || '-'} · ${c.kelas || '-'} · ${c.tingkat || '-'}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-slate-700">${(c.nilai_rata_rata || 0).toFixed(1)}</div><div class="text-xs text-slate-400">Nilai</div></div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-slate-700">${c.persentase_hadir || 0}%</div><div class="text-xs text-slate-400">Kehadiran</div></div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold ${(c.tunggakan || 0) > 0 ? 'text-red-600' : 'text-green-600'}">${formatRupiah(c.tunggakan || 0)}</div><div class="text-xs text-slate-400">Tunggakan</div></div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-slate-700">${c.ujian_mendatang || 0}</div><div class="text-xs text-slate-400">Ujian</div></div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-slate-700">${c.total_pertemuan || c.total_hadir || 0}</div><div class="text-xs text-slate-400">Pertemuan</div></div>
                    </div>
                </div>`).join('');
        } else {
            document.getElementById('childrenList').innerHTML = '<div class="card p-8 text-center"><p class="text-slate-500">Belum ada anak terdaftar.</p></div>';
        }
    } catch (e) {
        document.getElementById('childrenList').innerHTML = '<div class="card p-5 text-center text-red-500">Gagal memuat data anak.</div>';
    }
});
</script>
@endsection
