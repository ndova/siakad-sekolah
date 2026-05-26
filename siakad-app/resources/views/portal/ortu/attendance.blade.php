@extends('portal.layout')
@section('title', 'Presensi Anak — Portal Orang Tua')
@section('sidebar-nav')
<a href="/portal/ortu/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/ortu/children" class="sidebar-link"><i data-lucide="users" class="w-5 h-5"></i> Anak Saya</a>
<a href="/portal/ortu/attendance" class="sidebar-link active"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/ortu/bills" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/ortu/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800" id="pageTitle">Presensi</h1>
    <p class="text-slate-400 text-sm mt-1" id="pageSubtitle">Memuat...</p>
</div>

{{-- Alert Alfa --}}
<div id="alfaAlert" class="hidden mb-5 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3">
    <span class="text-xl">⚠️</span>
    <div>
        <p class="text-sm font-semibold text-red-700">Peringatan Kehadiran</p>
        <p class="text-sm text-red-600 mt-0.5" id="alfaMessage"></p>
    </div>
</div>

{{-- Ringkasan Bulan Ini --}}
<div id="bulanIniCard" class="card p-5 mb-5 hidden">
    <h3 class="text-sm font-semibold text-slate-500 mb-3">Bulan Ini</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3" id="bulanIniGrid"></div>
</div>

{{-- Ringkasan Semester --}}
<div class="card p-5 mb-5">
    <h3 class="text-sm font-semibold text-slate-500 mb-3">Ringkasan Semester</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4" id="semesterSummary"></div>
    <div class="flex items-center gap-3">
        <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden flex-1">
            <div id="semesterProgressBar" class="h-full rounded-full transition-all duration-700 bg-emerald-500" style="width:0%"></div>
        </div>
        <span class="text-lg font-bold whitespace-nowrap" id="semesterPct">0%</span>
    </div>
</div>

{{-- Tabs --}}
<div class="flex gap-1 mb-5 overflow-x-auto" id="tabBarOrtu">
    <button onclick="switchTabOrtu('calendar')" class="tab-btn-ortu active px-4 py-2 rounded-xl text-sm font-medium bg-indigo-600 text-white whitespace-nowrap">📅 Kalender</button>
    <button onclick="switchTabOrtu('riwayat')" class="tab-btn-ortu px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 whitespace-nowrap">📋 Riwayat</button>
    <button onclick="switchTabOrtu('perbulan')" class="tab-btn-ortu px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 whitespace-nowrap">📊 Per Bulan</button>
</div>

{{-- Tab Calendar --}}
<div id="tabOrtuCalendar" class="tab-content-ortu">
    <div class="card overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <button onclick="changeMonthOrtu(-1)" class="p-2 rounded-lg hover:bg-slate-100"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
            <span class="font-semibold text-slate-700" id="ortuCalMonthLabel">-</span>
            <button onclick="changeMonthOrtu(1)" class="p-2 rounded-lg hover:bg-slate-100"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
        </div>
        <div class="grid grid-cols-7 gap-px bg-slate-100 text-center text-xs font-semibold text-slate-500">
            <div class="py-2 bg-white">Min</div><div class="py-2 bg-white">Sen</div><div class="py-2 bg-white">Sel</div><div class="py-2 bg-white">Rab</div><div class="py-2 bg-white">Kam</div><div class="py-2 bg-white">Jum</div><div class="py-2 bg-white">Sab</div>
        </div>
        <div class="grid grid-cols-7 gap-px bg-slate-100" id="ortuCalGrid">
            <div class="bg-white py-2 text-center text-sm text-slate-300 col-span-7">Memuat...</div>
        </div>
        <div class="p-3 bg-slate-50 border-t flex flex-wrap gap-3 text-xs text-slate-500">
            <span>✅ Hadir</span><span>⏰ Terlambat</span><span>📝 Izin</span><span>🏥 Sakit</span><span>❌ Alfa</span><span>⬜ Tanpa Data</span>
        </div>
    </div>
</div>

{{-- Tab Riwayat --}}
<div id="tabOrtuRiwayat" class="tab-content-ortu hidden">
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <th class="text-left px-4 py-3 font-semibold">Tanggal</th>
                    <th class="text-left px-4 py-3 font-semibold">Mapel</th>
                    <th class="px-4 py-3 text-center font-semibold">Status</th>
                    <th class="text-left px-4 py-3 font-semibold">Keterangan</th>
                </tr></thead>
                <tbody id="ortuAttTable" class="divide-y divide-slate-50"></tbody>
            </table>
        </div>
        <div class="p-4 border-t flex justify-center gap-2" id="ortuPagination"></div>
    </div>
</div>

{{-- Tab Per Bulan --}}
<div id="tabOrtuPerBulan" class="tab-content-ortu hidden">
    <div id="perBulanContainer" class="space-y-3"></div>
</div>
@endsection
@section('scripts')
<script>
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
let ortuStudentId = null;
let ortuYearMonth = '';
let ortuCalendarData = [];
let ortuSummary = {};
let currentPage = 1;

function switchTabOrtu(tab) {
    document.querySelectorAll('.tab-content-ortu').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.tab-btn-ortu').forEach(b => { b.classList.remove('active','bg-indigo-600','text-white'); b.classList.add('bg-slate-100','text-slate-600'); });
    const cap = tab.charAt(0).toUpperCase() + tab.slice(1);
    document.getElementById('tabOrtu'+cap).classList.remove('hidden');
    event.target.classList.add('active','bg-indigo-600','text-white');
    event.target.classList.remove('bg-slate-100','text-slate-600');
}

function renderOrtuCalendar() {
    if (!ortuCalendarData.length) return;
    const [y,m] = ortuYearMonth.split('-');
    document.getElementById('ortuCalMonthLabel').textContent = `${MONTHS[parseInt(m)-1]} ${y}`;
    const firstDay = new Date(y, parseInt(m)-1, 1).getDay();
    const grid = document.getElementById('ortuCalGrid');
    let html = '';
    for (let i=0; i<firstDay; i++) html += '<div class="bg-white py-2"></div>';
    ortuCalendarData.forEach(d => {
        const bgClass = d.color === 'green' ? 'bg-emerald-50' : d.color === 'yellow' ? 'bg-yellow-50' : d.color === 'amber' ? 'bg-amber-50' : d.color === 'orange' ? 'bg-orange-50' : d.color === 'red' ? 'bg-red-50' : '';
        const icon = d.color === 'green' ? '✅' : d.color === 'yellow' ? '⏰' : d.color === 'amber' ? '📝' : d.color === 'orange' ? '🏥' : d.color === 'red' ? '❌' : '·';
        html += `<div class="bg-white py-2 text-center ${bgClass}" title="${d.date}${d.label?': '+d.label:''}"><span class="text-xs text-slate-400">${d.day}</span><div class="text-sm">${icon}</div></div>`;
    });
    grid.innerHTML = html;
}

function changeMonthOrtu(delta) {
    const [y,m] = ortuYearMonth.split('-').map(Number);
    const d = new Date(y, m-1+delta, 1);
    loadOrtuData(d.getFullYear(), String(d.getMonth()+1).padStart(2,'0'));
}

async function loadOrtuData(year, month) {
    if (!ortuStudentId) return;
    ortuYearMonth = `${year}-${month}`;
    try {
        const data = await apiFetch(`${API_BASE}/guardian/children/${ortuStudentId}/attendance?year_month=${ortuYearMonth}`);
        ortuCalendarData = data.calendar || [];
        ortuSummary = data.summary || {};

        document.getElementById('pageTitle').textContent = `Presensi — ${data.student?.nama || ''}`;
        document.getElementById('pageSubtitle').textContent = `${data.student?.nis || ''} · ${data.student?.kelas || ''} · ${data.semester?.label || ''}`;

        // Alert alfa
        const alerts = data.alerts || {};
        const alertDiv = document.getElementById('alfaAlert');
        if (alerts.alfa_alert) {
            alertDiv.classList.remove('hidden');
            document.getElementById('alfaMessage').textContent = alerts.alfa_message;
        } else {
            alertDiv.classList.add('hidden');
        }

        // Bulan ini quick indicator
        const bulanIni = data.bulan_ini;
        const biCard = document.getElementById('bulanIniCard');
        if (bulanIni) {
            biCard.classList.remove('hidden');
            document.getElementById('bulanIniGrid').innerHTML = `
                <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-emerald-600">${bulanIni.hadir||0}</div><div class="text-xs text-slate-400">✅ Hadir</div></div>
                <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-yellow-600">${bulanIni.terlambat||0}</div><div class="text-xs text-slate-400">⏰ Terlambat</div></div>
                <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-amber-600">${bulanIni.izin||0}</div><div class="text-xs text-slate-400">📝 Izin</div></div>
                <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-orange-600">${bulanIni.sakit||0}</div><div class="text-xs text-slate-400">🏥 Sakit</div></div>
                <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-red-600">${bulanIni.alfa||0}</div><div class="text-xs text-slate-400">❌ Alfa</div></div>`;
        } else {
            biCard.classList.add('hidden');
        }

        // Semester summary
        document.getElementById('semesterSummary').innerHTML = `
            <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-emerald-600">${ortuSummary.hadir||0}</div><div class="text-xs text-slate-400">✅ Hadir</div></div>
            <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-yellow-600">${ortuSummary.terlambat||0}</div><div class="text-xs text-slate-400">⏰ Terlambat</div></div>
            <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-amber-600">${ortuSummary.izin||0}</div><div class="text-xs text-slate-400">📝 Izin</div></div>
            <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-orange-600">${ortuSummary.sakit||0}</div><div class="text-xs text-slate-400">🏥 Sakit</div></div>
            <div class="bg-slate-50 rounded-xl p-3 text-center"><div class="text-lg font-bold text-red-600">${ortuSummary.alfa||0}</div><div class="text-xs text-slate-400">❌ Alfa</div></div>`;
        const pct = ortuSummary.persentase_hadir || 0;
        document.getElementById('semesterPct').textContent = pct+'%';
        const bar = document.getElementById('semesterProgressBar');
        bar.style.width = pct+'%';
        bar.className = 'h-full rounded-full transition-all duration-700 '+(pct>=90?'bg-emerald-500':pct>=75?'bg-amber-500':'bg-red-500');

        renderOrtuCalendar();

        // Per bulan tab
        const perBulan = data.per_bulan || [];
        const pbContainer = document.getElementById('perBulanContainer');
        if (perBulan.length) {
            pbContainer.innerHTML = perBulan.map(b => {
                const pct2 = b.persentase_hadir || 0;
                const barColor = pct2 >= 90 ? 'bg-emerald-500' : pct2 >= 75 ? 'bg-amber-500' : 'bg-red-500';
                const [by,bm] = b.bulan.split('-');
                return `<div class="card p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-sm text-slate-700">${MONTHS[parseInt(bm)-1]} ${by}</span>
                        <span class="text-sm font-bold ${pct2>=90?'text-emerald-600':pct2>=75?'text-amber-600':'text-red-600'}">${pct2}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 mb-2"><div class="h-full rounded-full ${barColor}" style="width:${pct2}%"></div></div>
                    <div class="flex gap-3 text-xs text-slate-400">
                        <span>✅ ${b.hadir}</span><span>⏰ ${b.terlambat}</span><span>📝 ${b.izin}</span><span>🏥 ${b.sakit}</span><span>❌ ${b.alfa}</span>
                    </div>
                </div>`;
            }).join('');
        } else {
            pbContainer.innerHTML = '<div class="card p-8 text-center text-slate-400">Belum ada data per bulan.</div>';
        }

        await loadOrtuRiwayat(1);
    } catch(e) {
        document.getElementById('ortuCalGrid').innerHTML = '<div class="bg-white py-8 text-center text-sm text-red-500 col-span-7">Gagal memuat data.</div>';
    }
}

async function loadOrtuRiwayat(page=1) {
    if (!ortuStudentId) return;
    try {
        const data = await apiFetch(`${API_BASE}/guardian/children/${ortuStudentId}/attendance?per_page=15&page=${page}`);
        const records = data.data || [];
        const meta = data.meta || {};
        const tbody = document.getElementById('ortuAttTable');
        if (records.length) {
            tbody.innerHTML = records.map(r => {
                const c = r.color || 'slate';
                return `<tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-2.5 font-medium text-slate-700">${r.tanggal}<br><span class="text-xs text-slate-400">${r.hari||''}</span></td>
                    <td class="px-4 py-2.5 text-sm text-slate-600">${r.subject || '-'}</td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-${c}-100 text-${c}-700">${r.icon||''} ${r.label||r.status}</span></td>
                    <td class="px-4 py-2.5 text-sm text-slate-500">${r.keterangan || '-'}</td>
                </tr>`;
            }).join('');
            let pagHtml = '';
            if (meta.last_page > 1) {
                for (let i=1; i<=meta.last_page; i++) {
                    pagHtml += `<button onclick="loadOrtuRiwayat(${i})" class="px-3 py-1.5 rounded-lg text-sm ${i===meta.current_page?'bg-indigo-600 text-white':'bg-slate-100 text-slate-600 hover:bg-slate-200'}">${i}</button>`;
                }
            }
            document.getElementById('ortuPagination').innerHTML = pagHtml;
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data presensi.</td></tr>';
            document.getElementById('ortuPagination').innerHTML = '';
        }
    } catch(e) {
        document.getElementById('ortuAttTable').innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-red-500">Gagal memuat data.</td></tr>';
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const children = await apiFetch(`${API_BASE}/guardian/children`);
        const childList = children.data || [];
        if (!childList.length) {
            document.getElementById('ortuCalGrid').innerHTML = '<div class="bg-white py-8 text-center text-sm text-slate-400 col-span-7">Tidak ada anak terdaftar.</div>';
            return;
        }
        ortuStudentId = childList[0].id;
        loadOrtuData(new Date().getFullYear(), String(new Date().getMonth()+1).padStart(2,'0'));
    } catch(e) {
        document.getElementById('ortuCalGrid').innerHTML = '<div class="bg-white py-8 text-center text-sm text-red-500 col-span-7">Gagal memuat data.</div>';
    }
});
</script>
@endsection
