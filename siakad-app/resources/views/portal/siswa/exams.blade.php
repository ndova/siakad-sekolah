@extends('portal.layout')
@section('title', 'Ujian — Portal Siswa')
@section('sidebar-nav')
<a href="/portal/siswa/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/siswa/grades" class="sidebar-link"><i data-lucide="bar-chart-3" class="w-5 h-5"></i> Nilai & Rapor</a>
<a href="/portal/siswa/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/siswa/exams" class="sidebar-link active"><i data-lucide="file-text" class="w-5 h-5"></i> Ujian</a>
<a href="/portal/siswa/payments" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/siswa/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Ujian</h1>
    <p class="text-slate-400 text-sm mt-1">Jadwal ujian Anda</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="examCards">
    <div class="card p-5 animate-pulse"><div class="h-4 bg-slate-100 rounded w-3/4 mb-3"></div><div class="h-3 bg-slate-100 rounded w-1/2 mb-2"></div><div class="h-3 bg-slate-100 rounded w-1/3"></div></div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/student/exam/schedule`);
        const exams = data.data || [];
        const el = document.getElementById('examCards');
        if (exams.length) {
            el.innerHTML = exams.map(e => {
                const start = new Date(e.start_time), end = new Date(e.end_time);
                const now = new Date();
                const isActive = now >= start && now <= end;
                return `<div class="card p-5">
                    <div class="flex items-start justify-between mb-3">
                        <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold ${isActive ? 'bg-green-100 text-green-700' : 'bg-primary-100 text-primary-700'}">${isActive ? 'SEDANG BERLANGSUNG' : e.type?.toUpperCase() || 'UJIAN'}</span>
                        <span class="text-xs text-slate-400">${e.duration} menit</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 mb-2">${e.title}</h3>
                    <div class="space-y-2 text-xs text-slate-500">
                        <div class="flex items-center gap-2"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> ${start.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric'})}</div>
                        <div class="flex items-center gap-2"><i data-lucide="clock" class="w-3.5 h-3.5"></i> ${start.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})} - ${end.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</div>
                        <div class="flex items-center gap-2"><i data-lucide="help-circle" class="w-3.5 h-3.5"></i> ${e.total_questions || 0} soal</div>
                    </div>
                </div>`;
            }).join('');
        } else {
            el.innerHTML = '<div class="card p-8 text-center col-span-full"><div class="text-4xl mb-3">📝</div><p class="text-slate-500 font-medium">Tidak ada ujian</p><p class="text-sm text-slate-400 mt-1">Belum ada jadwal ujian untuk saat ini.</p></div>';
        }
        lucide.createIcons();
    } catch (e) { document.getElementById('examCards').innerHTML = '<div class="card p-5 col-span-full text-center text-red-500">Gagal memuat jadwal ujian.</div>'; }
});
</script>
@endsection
