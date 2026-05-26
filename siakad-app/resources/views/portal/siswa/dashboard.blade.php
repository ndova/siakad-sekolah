@extends('portal.layout')
@section('title', 'Dashboard — Portal Siswa')
@section('sidebar-nav')
<a href="/portal/siswa/dashboard" class="sidebar-link active"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/siswa/grades" class="sidebar-link"><i data-lucide="bar-chart-3" class="w-5 h-5"></i> Nilai & Rapor</a>
<a href="/portal/siswa/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/siswa/exams" class="sidebar-link"><i data-lucide="file-text" class="w-5 h-5"></i> Ujian</a>
<a href="/portal/siswa/payments" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/siswa/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
    <p class="text-slate-400 text-sm mt-1">Selamat datang, <span id="studentName">Siswa</span>!</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card p-5">
        <div class="flex items-center gap-3 mb-2"><div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"><i data-lucide="trending-up" class="w-5 h-5 text-blue-600"></i></div></div>
        <div class="text-3xl font-bold text-slate-800" id="avgGrade">--</div>
        <div class="text-xs text-slate-400 mt-1">Nilai Rata-Rata</div>
    </div>
    <div class="card p-5">
        <div class="flex items-center gap-3 mb-2"><div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center"><i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i></div></div>
        <div class="text-3xl font-bold text-slate-800" id="attendancePct">--%</div>
        <div class="text-xs text-slate-400 mt-1">Kehadiran</div>
    </div>
    <div class="card p-5">
        <div class="flex items-center gap-3 mb-2"><div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center"><i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i></div></div>
        <div class="text-3xl font-bold text-slate-800" id="pendingBills">Rp 0</div>
        <div class="text-xs text-slate-400 mt-1">Tunggakan</div>
    </div>
    <div class="card p-5">
        <div class="flex items-center gap-3 mb-2"><div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center"><i data-lucide="clock" class="w-5 h-5 text-purple-600"></i></div></div>
        <div class="text-3xl font-bold text-slate-800" id="upcomingExams">0</div>
        <div class="text-xs text-slate-400 mt-1">Ujian Mendatang</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2"><i data-lucide="book-open" class="w-4 h-4 text-primary-500"></i> Jadwal Hari Ini</h3>
        <div id="todaySchedule" class="space-y-3">
            <div class="animate-pulse space-y-2"><div class="h-4 bg-slate-100 rounded w-3/4"></div><div class="h-4 bg-slate-100 rounded w-1/2"></div><div class="h-4 bg-slate-100 rounded w-2/3"></div></div>
        </div>
    </div>
    <div class="card p-5">
        <h3 class="font-semibold text-slate-700 mb-4 flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-amber-500"></i> Ujian Mendatang</h3>
        <div id="examList" class="space-y-3">
            <div class="animate-pulse space-y-2"><div class="h-4 bg-slate-100 rounded w-full"></div><div class="h-4 bg-slate-100 rounded w-3/4"></div></div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/student/dashboard`);
        const d = data.data || data;
        document.getElementById('studentName').textContent = d.student?.nama || 'Siswa';
        document.getElementById('avgGrade').textContent = (d.akademik?.nilai_rata_rata || 0).toFixed(1);
        const attPct = d.akademik?.persentase_hadir || 0;
        document.getElementById('attendancePct').textContent = attPct + '%';
        document.getElementById('pendingBills').textContent = formatRupiah(d.keuangan?.tunggakan || 0);
        document.getElementById('upcomingExams').textContent = (d.ujian_mendatang || []).length;

        // Today's schedule
        const scheduleEl = document.getElementById('todaySchedule');
        const schedule = d.jadwal_hari_ini || [];
        if (schedule.length) {
            scheduleEl.innerHTML = schedule.map(s => `<div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50"><div class="w-2 h-2 rounded-full bg-primary-500"></div><div><div class="text-sm font-medium text-slate-700">${s.subject}</div><div class="text-xs text-slate-400">${s.teacher}</div></div></div>`).join('');
        } else { scheduleEl.innerHTML = '<p class="text-sm text-slate-400">Tidak ada jadwal hari ini.</p>'; }

        // Upcoming exams
        const examEl = document.getElementById('examList');
        const exams = d.ujian_mendatang || [];
        if (exams.length) {
            examEl.innerHTML = exams.map(e => {
                const start = new Date(e.start_time);
                return `<div class="flex items-start gap-3 p-3 rounded-lg border border-slate-100"><div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0"><i data-lucide="alarm-clock" class="w-4 h-4 text-amber-600"></i></div><div><div class="text-sm font-semibold text-slate-700">${e.title}</div><div class="text-xs text-slate-400">${start.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric'})} · ${e.duration} menit</div></div></div>`;
            }).join('');
        } else { examEl.innerHTML = '<p class="text-sm text-slate-400">Tidak ada ujian mendatang.</p>'; }

        lucide.createIcons();
    } catch (e) {
        console.error('Dashboard error:', e);
        document.getElementById('studentName').textContent = 'Gagal memuat data';
    }
});
</script>
@endsection
