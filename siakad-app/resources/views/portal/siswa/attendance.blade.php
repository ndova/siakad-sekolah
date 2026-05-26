@extends('portal.layout')
@section('title', 'Presensi — Portal Siswa')
@section('sidebar-nav')
<a href="/portal/siswa/dashboard" class="sidebar-link"><i data-lucide="home" class="w-5 h-5"></i> Dashboard</a>
<a href="/portal/siswa/grades" class="sidebar-link"><i data-lucide="bar-chart-3" class="w-5 h-5"></i> Nilai & Rapor</a>
<a href="/portal/siswa/attendance" class="sidebar-link active"><i data-lucide="calendar-check" class="w-5 h-5"></i> Presensi</a>
<a href="/portal/siswa/exams" class="sidebar-link"><i data-lucide="file-text" class="w-5 h-5"></i> Ujian</a>
<a href="/portal/siswa/payments" class="sidebar-link"><i data-lucide="credit-card" class="w-5 h-5"></i> Pembayaran</a>
<a href="/portal/siswa/profile" class="sidebar-link"><i data-lucide="user" class="w-5 h-5"></i> Profil</a>
@endsection
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Presensi</h1>
    <p class="text-slate-400 text-sm mt-1">Riwayat kehadiran Anda</p>
</div>

{{-- Ringkasan Kehadiran --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6" id="attSummaryCards">
    <div class="card p-4 text-center"><div class="text-2xl font-bold text-emerald-600" id="sumHadir">--</div><div class="text-xs text-slate-400 mt-1">✅ Hadir</div></div>
    <div class="card p-4 text-center"><div class="text-2xl font-bold text-yellow-600" id="sumTerlambat">--</div><div class="text-xs text-slate-400 mt-1">⏰ Terlambat</div></div>
    <div class="card p-4 text-center"><div class="text-2xl font-bold text-amber-500" id="sumIzin">--</div><div class="text-xs text-slate-400 mt-1">📝 Izin</div></div>
    <div class="card p-4 text-center"><div class="text-2xl font-bold text-orange-500" id="sumSakit">--</div><div class="text-xs text-slate-400 mt-1">🏥 Sakit</div></div>
    <div class="card p-4 text-center"><div class="text-2xl font-bold text-red-500" id="sumAlfa">--</div><div class="text-xs text-slate-400 mt-1">❌ Alfa</div></div>
</div>

{{-- Progress Bar Kehadiran --}}
<div class="card p-5 mb-6">
    <div class="flex justify-between items-end mb-2">
        <span class="text-sm font-semibold text-slate-700">Tingkat Kehadiran Semester Ini</span>
        <span class="text-2xl font-bold" id="attPercent">--%</span>
    </div>
    <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden">
        <div id="attProgressBar" class="h-full rounded-full transition-all duration-700 bg-emerald-500" style="width: 0%"></div>
    </div>
</div>

{{-- Tabs --}}
<div class="flex gap-1 mb-5 overflow-x-auto" id="tabBar">
    <button onclick="switchTab('calendar')" class="tab-btn active px-4 py-2 rounded-xl text-sm font-medium bg-indigo-600 text-white whitespace-nowrap">📅 Kalender</button>
    <button onclick="switchTab('riwayat')" class="tab-btn px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 whitespace-nowrap">📋 Riwayat</button>
    <button onclick="switchTab('permapel')" class="tab-btn px-4 py-2 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 whitespace-nowrap">📚 Per Mapel</button>
</div>

{{-- Tab: Kalender --}}
<div id="tabCalendar" class="tab-content">
    <div class="card overflow-hidden mb-5">
        <div class="p-4 border-b flex justify-between items-center">
            <button onclick="changeMonth(-1)" class="p-2 rounded-lg hover:bg-slate-100"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
            <span class="font-semibold text-slate-700" id="calMonthLabel">-</span>
            <button onclick="changeMonth(1)" class="p-2 rounded-lg hover:bg-slate-100"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
        </div>
        <div class="grid grid-cols-7 gap-px bg-slate-100 text-center text-xs font-semibold text-slate-500">
            <div class="py-2 bg-white">Min</div><div class="py-2 bg-white">Sen</div><div class="py-2 bg-white">Sel</div><div class="py-2 bg-white">Rab</div><div class="py-2 bg-white">Kam</div><div class="py-2 bg-white">Jum</div><div class="py-2 bg-white">Sab</div>
        </div>
        <div class="grid grid-cols-7 gap-px bg-slate-100" id="calGrid">
            <div class="bg-white py-2 text-center text-sm text-slate-300">Memuat...</div>
        </div>
        <div class="p-3 bg-slate-50 border-t flex flex-wrap gap-3 text-xs text-slate-500">
            <span>✅ Hadir</span><span>⏰ Terlambat</span><span>📝 Izin</span><span>🏥 Sakit</span><span>❌ Alfa</span><span>⬜ Tanpa Data</span>
        </div>
    </div>
</div>

{{-- Tab: Riwayat --}}
<div id="tabRiwayat" class="tab-content hidden">
    <div class="card overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <th class="text-left px-4 py-3 font-semibold">Tanggal</th>
                    <th class="text-left px-4 py-3 font-semibold">Mapel</th>
                    <th class="px-4 py-3 text-center font-semibold">Status</th>
                    <th class="text-left px-4 py-3 font-semibold">Keterangan</th>
                </tr></thead>
                <tbody id="attTable" class="divide-y divide-slate-50">
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t flex justify-center gap-2" id="pagination"></div>
    </div>
</div>

{{-- Tab: Per Mapel --}}
<div id="tabPerMapel" class="tab-content hidden">
    <div id="perMapelContainer" class="space-y-3">
        <div class="card p-5 animate-pulse"><div class="h-4 bg-slate-100 rounded w-1/3 mb-2"></div><div class="h-3 bg-slate-100 rounded w-full"></div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>
let currentYearMonth = '';
let calendarData = [];
let summaryData = {};
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('active','bg-indigo-600','text-white'); b.classList.add('bg-slate-100','text-slate-600'); });
    document.getElementById('tab'+tab.charAt(0).toUpperCase()+tab.slice(1)).classList.remove('hidden');
    event.target.classList.add('active','bg-indigo-600','text-white');
    event.target.classList.remove('bg-slate-100','text-slate-600');
}

function renderCalendar() {
    if (!calendarData.length) return;
    const [y,m] = currentYearMonth.split('-');
    document.getElementById('calMonthLabel').textContent = `${MONTHS[parseInt(m)-1]} ${y}`;

    const firstDay = new Date(y, parseInt(m)-1, 1).getDay();
    const grid = document.getElementById('calGrid');
    let html = '';
    for (let i=0; i<firstDay; i++) html += '<div class="bg-white py-2"></div>';

    calendarData.forEach(d => {
        const isToday = d.date === new Date().toISOString().slice(0,10);
        const bgClass = d.has_data ? (
            d.records?.[0]?.status === 'hadir' ? 'bg-emerald-50' :
            d.records?.[0]?.status === 'terlambat' ? 'bg-yellow-50' :
            d.records?.[0]?.status === 'izin' ? 'bg-amber-50' :
            d.records?.[0]?.status === 'sakit' ? 'bg-orange-50' :
            d.records?.[0]?.status === 'alfa' || d.records?.[0]?.status === 'tidak_hadir' ? 'bg-red-50' : ''
        ) : '';
        const icon = d.has_data ? (d.records?.[0]?.icon || '⬜') : '·';
        html += `<div class="bg-white py-2 text-center ${bgClass} ${isToday?'ring-1 ring-indigo-300':''}" title="${d.date}${d.has_data ? ': ' + d.records.map(r=>r.label).join(', ') : ''}">
            <span class="text-xs ${isToday?'font-bold text-indigo-600':'text-slate-400'}">${d.day}</span>
            <div class="text-sm">${icon}</div>
        </div>`;
    });
    grid.innerHTML = html;
}

function changeMonth(delta) {
    const [y,m] = currentYearMonth.split('-').map(Number);
    const d = new Date(y, m-1+delta, 1);
    const ny = d.getFullYear(), nm = String(d.getMonth()+1).padStart(2,'0');
    loadData(`${ny}-${nm}`);
}

async function loadData(ym) {
    currentYearMonth = ym || new Date().toISOString().slice(0,7);
    try {
        const data = await apiFetch(`${API_BASE}/student/attendance/summary?year_month=${currentYearMonth}`);
        summaryData = data.summary || {};
        calendarData = data.calendar || [];

        // Update summary cards
        document.getElementById('sumHadir').textContent = summaryData.hadir || 0;
        document.getElementById('sumTerlambat').textContent = summaryData.terlambat || 0;
        document.getElementById('sumIzin').textContent = summaryData.izin || 0;
        document.getElementById('sumSakit').textContent = summaryData.sakit || 0;
        document.getElementById('sumAlfa').textContent = summaryData.alfa || 0;

        // Progress bar
        const pct = summaryData.persentase_hadir || 0;
        document.getElementById('attPercent').textContent = pct + '%';
        const bar = document.getElementById('attProgressBar');
        bar.style.width = pct + '%';
        bar.className = 'h-full rounded-full transition-all duration-700 ' + (pct >= 90 ? 'bg-emerald-500' : pct >= 75 ? 'bg-amber-500' : 'bg-red-500');

        renderCalendar();

        // Per mapel
        const perMapel = data.per_mapel || [];
        const pmContainer = document.getElementById('perMapelContainer');
        if (perMapel.length) {
            pmContainer.innerHTML = perMapel.map(m => {
                const pct2 = m.persentase_hadir || 0;
                const barColor = pct2 >= 90 ? 'bg-emerald-500' : pct2 >= 75 ? 'bg-amber-500' : 'bg-red-500';
                return `<div class="card p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-sm text-slate-700">${m.subject}</span>
                        <span class="text-sm font-bold ${pct2 >= 90 ? 'text-emerald-600': pct2 >= 75 ? 'text-amber-600' : 'text-red-600'}">${pct2}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 mb-2"><div class="h-full rounded-full ${barColor}" style="width:${pct2}%"></div></div>
                    <div class="flex gap-3 text-xs text-slate-400">
                        <span>✅ ${m.hadir}</span><span>⏰ ${m.terlambat}</span><span>📝 ${m.izin}</span><span>🏥 ${m.sakit}</span><span>❌ ${m.alfa}</span>
                    </div>
                </div>`;
            }).join('');
        } else {
            pmContainer.innerHTML = '<div class="card p-8 text-center text-slate-400">Belum ada data presensi per mapel.</div>';
        }

        // Riwayat table
        await loadRiwayat(1);

    } catch(e) {
        document.getElementById('calGrid').innerHTML = '<div class="bg-white py-8 text-center text-sm text-red-500 col-span-7">Gagal memuat data.</div>';
        document.getElementById('attTable').innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-red-500">Gagal memuat data.</td></tr>';
        document.getElementById('perMapelContainer').innerHTML = '<div class="card p-5 text-center text-red-500">Gagal memuat data.</div>';
    }
}

async function loadRiwayat(page=1) {
    try {
        const data = await apiFetch(`${API_BASE}/student/attendance?per_page=15&page=${page}`);
        const records = data.data || [];
        const meta = data.meta || {};
        const tbody = document.getElementById('attTable');

        if (records.length) {
            tbody.innerHTML = records.map(r => {
                const c = r.color || 'slate';
                return `<tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-2.5 font-medium text-slate-700">${r.tanggal || '-'}<br><span class="text-xs text-slate-400">${r.hari||''}</span></td>
                    <td class="px-4 py-2.5 text-sm text-slate-600">${r.subject || '-'}</td>
                    <td class="px-4 py-2.5 text-center"><span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-${c}-100 text-${c}-700">${r.icon||''} ${r.label||r.status}</span></td>
                    <td class="px-4 py-2.5 text-sm text-slate-500">${r.keterangan || '-'}</td>
                </tr>`;
            }).join('');

            // Pagination
            let pagHtml = '';
            if (meta.last_page > 1) {
                for (let i=1; i<=meta.last_page; i++) {
                    pagHtml += `<button onclick="loadRiwayat(${i})" class="px-3 py-1.5 rounded-lg text-sm ${i===meta.current_page ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}">${i}</button>`;
                }
            }
            document.getElementById('pagination').innerHTML = pagHtml;
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data presensi.</td></tr>';
            document.getElementById('pagination').innerHTML = '';
        }
    } catch(e) {
        document.getElementById('attTable').innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-red-500">Gagal memuat data.</td></tr>';
    }
}

document.addEventListener('DOMContentLoaded', () => loadData());
</script>
@endsection
