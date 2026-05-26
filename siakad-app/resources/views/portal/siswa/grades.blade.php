@extends('portal.layout')
@section('title', 'Nilai & Rapor — Portal Siswa')
@section('sidebar-nav')
<a href="/portal/siswa/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/siswa/grades" class="sidebar-link active"><i data-lucide="bar-chart-3" class="w-5 h-5"></i> Nilai & Rapor</a>
<a href="/portal/siswa/attendance" class="sidebar-link"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/siswa/exams" class="sidebar-link"><i data-lucide="file-text" class="w-5 h-5"></i> Ujian</a>
<a href="/portal/siswa/payments" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/siswa/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Nilai Intrakurikuler</h1>
    <p class="text-slate-400 text-sm mt-1">Nilai per Tujuan Pembelajaran (TP) — Kurikulum Merdeka</p>
</div>

<div id="gradesLoading" class="animate-pulse space-y-4">
    <div class="h-12 bg-slate-100 rounded-xl"></div>
    <div class="h-64 bg-slate-100 rounded-xl"></div>
</div>

<div id="gradesContent" class="hidden">
    <div class="card overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-slate-500 text-xs uppercase"><th class="text-left px-4 py-3 font-semibold">Mata Pelajaran</th><th class="px-4 py-3 font-semibold text-center">Formatif</th><th class="px-4 py-3 font-semibold text-center">Sumatif</th><th class="px-4 py-3 font-semibold text-center">Rata-Rata</th><th class="px-4 py-3 font-semibold text-center">Status</th></tr></thead>
                <tbody id="gradesTable" class="divide-y divide-slate-50"></tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="font-semibold text-slate-700 mb-3">Detail per TP — <span id="detailSubject" class="text-primary-600">Pilih mapel</span></h3>
        <div id="tpDetail" class="overflow-x-auto">
            <p class="text-sm text-slate-400">Klik baris mapel untuk melihat detail TP.</p>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
let allGradesData = [];
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await apiFetch(`${API_BASE}/student/grades`);
        const subjects = data.data || [];
        allGradesData = subjects;

        const tbody = document.getElementById('gradesTable');
        tbody.innerHTML = subjects.map((s, i) => {
            const subjGrades = s.tp_details || [];
            const formatif = subjGrades.filter(g => g.jenis === 'formatif');
            const sumatif = subjGrades.filter(g => g.jenis === 'sumatif');
            const avgF = formatif.length ? (formatif.reduce((a,b) => a + (b.nilai||0), 0) / formatif.length).toFixed(1) : '-';
            const avgS = sumatif.length ? (sumatif.reduce((a,b) => a + (b.nilai||0), 0) / sumatif.length).toFixed(1) : '-';
            const allNilai = subjGrades.map(g => g.nilai || 0);
            const avg = allNilai.length ? (allNilai.reduce((a,b)=>a+b,0)/allNilai.length).toFixed(1) : '-';
            const tuntas = parseFloat(avg) >= (s.kkm || 70);
            return `<tr class="cursor-pointer hover:bg-slate-50 transition" onclick="showTPDetail(${i})">
                <td class="px-4 py-3 font-medium text-slate-700">${s.subject_name || s.subject}</td>
                <td class="px-4 py-3 text-center">${avgF}</td>
                <td class="px-4 py-3 text-center">${avgS}</td>
                <td class="px-4 py-3 text-center font-bold">${avg}</td>
                <td class="px-4 py-3 text-center"><span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold ${tuntas ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">${tuntas ? 'Tuntas' : 'Remedial'}</span></td></tr>`;
        }).join('');

        document.getElementById('gradesLoading').classList.add('hidden');
        document.getElementById('gradesContent').classList.remove('hidden');
        lucide.createIcons();
    } catch (e) { document.getElementById('gradesLoading').innerHTML = '<p class="text-red-500 text-sm">Gagal memuat nilai.</p>'; }
});

function showTPDetail(i) {
    const s = allGradesData[i];
    const tps = s.tp_details || [];
    document.getElementById('detailSubject').textContent = s.subject_name || s.subject;
    if (tps.length) {
        document.getElementById('tpDetail').innerHTML = `<table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-slate-500 text-xs uppercase"><th class="text-left px-3 py-2">TP</th><th class="px-3 py-2 text-center">Nilai</th><th class="px-3 py-2 text-center">Jenis</th><th class="px-3 py-2 text-center">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-50">${tps.map(g => {
                const tuntas = (g.nilai||0) >= (s.kkm||70);
                return `<tr><td class="px-3 py-2 text-slate-600">${g.tp_code || 'TP'} ${g.tp_desc || ''}</td><td class="px-3 py-2 text-center font-semibold">${g.nilai || '-'}</td><td class="px-3 py-2 text-center text-xs text-slate-400">${g.jenis === 'formatif' ? 'Formatif' : 'Sumatif'}</td><td class="px-3 py-2 text-center"><span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold ${tuntas ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">${tuntas ? 'Tuntas' : 'Remedial'}</span></td></tr>`;
            }).join('')}</tbody></table>`;
    } else {
        document.getElementById('tpDetail').innerHTML = '<p class="text-sm text-slate-400">Belum ada data TP.</p>';
    }
}
</script>
@endsection
