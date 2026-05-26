<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIAKAD') — {{ config('app.siakad_school_name', 'Sekolah') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f1f5f9;
            --sidebar-active: #f1f5f9;
            --accent: #059669;
            --accent-light: #34d399;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; color: #1e293b; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            min-width: 260px;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e2e8f0;
            position: relative;
            z-index: 30;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 20px 18px;
            border-bottom: 1px solid #e2e8f0;
        }
        .sidebar-brand-icon {
            width: 40px; height: 40px;
            background: var(--accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 17px;
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }
        .sidebar-brand-text {
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }
        .sidebar-brand-sub {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 500;
        }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 12px; }
        .sidebar-section {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 18px 12px 8px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
            letter-spacing: -0.1px;
            margin-bottom: 2px;
        }
        .sidebar-link:hover { background: var(--sidebar-hover); color: #0f172a; }
        .sidebar-link.active {
            background: color-mix(in srgb, var(--accent) 15%, transparent);
            color: var(--accent);
            font-weight: 600;
        }
        .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.6; }
        .sidebar-link.active svg { opacity: 1; color: var(--accent); }
        .sidebar-link:hover svg { opacity: 0.8; }

        /* Header */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }
        .topbar h1 { font-size: 18px; font-weight: 600; color: #0f172a; letter-spacing: -0.3px; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 13px;
        }
        .role-badge {
            font-size: 11px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            letter-spacing: -0.2px;
        }
        .logout-btn {
            font-size: 13px;
            color: #94a3b8;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.15s;
        }
        .logout-btn:hover { color: #ef4444; background: #fef2f2; }

        /* Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .page-content { flex: 1; overflow-y: auto; padding: 24px; }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -280px; top: 0; bottom: 0; transition: left 0.3s ease; z-index: 50; }
            .sidebar.open { left: 0; box-shadow: 4px 0 25px rgba(0,0,0,0.1); }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }
            .sidebar-overlay.show { display: block; }
            .topbar { padding: 10px 16px; flex-wrap: wrap; gap: 8px; }
            .topbar h1 { font-size: 16px; }
            .page-content { padding: 16px; }
            .topbar-right { gap: 8px; }
            .role-badge { display: none; }
            .modal-box { max-width: 95vw !important; margin: 10px; }
            /* Responsive table wrapper */
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            /* Filter form stacking */
            .filter-form { flex-direction: column; }
            .filter-form input, .filter-form select { width: 100% !important; }
        }

        /* Hamburger button (visible only on mobile) */
        .hamburger-btn { display: none; background: none; border: none; cursor: pointer; color: #64748b; padding: 4px; }
        @media (max-width: 768px) {
            .hamburger-btn { display: flex; align-items: center; justify-content: center; }
        }

        /* Theme Picker */
        .theme-btn {
            width: 28px; height: 28px;
            border-radius: 8px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            position: relative;
        }
        .theme-btn:hover { transform: scale(1.15); }
        .theme-btn.active { border-color: #0f172a; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0f172a; }
        .theme-btn.active::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        #themePanel {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 16px;
            min-width: 220px;
            z-index: 100;
            display: none;
        }
        #themePanel.show { display: block; }
        #themePanel h4 { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
        .theme-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
        .theme-label { font-size: 10px; color: #94a3b8; text-align: center; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ─── Themed Utility Classes ─────────────────────────── */
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { filter: brightness(1.1); }
        .btn-accent-soft { background: color-mix(in srgb, var(--accent) 15%, transparent); color: var(--accent); }
        .btn-accent-soft:hover { background: color-mix(in srgb, var(--accent) 25%, transparent); }
        .text-accent { color: var(--accent); }
        .text-accent-light { color: var(--accent-light); }
        .bg-accent-50 { background: color-mix(in srgb, var(--accent) 10%, transparent); }
        .bg-accent-100 { background: color-mix(in srgb, var(--accent) 20%, transparent); }
        .border-accent { border-color: var(--accent); }
        .border-accent-100 { border-color: color-mix(in srgb, var(--accent) 20%, transparent); }
        .border-accent-200 { border-color: color-mix(in srgb, var(--accent) 30%, transparent); }
        .border-accent-300 { border-color: color-mix(in srgb, var(--accent) 45%, transparent); }
        .ring-accent-200 { --tw-ring-color: color-mix(in srgb, var(--accent) 30%, transparent); }
        .ring-accent-300 { --tw-ring-color: color-mix(in srgb, var(--accent) 45%, transparent); }
        .ring-accent-500 { --tw-ring-color: color-mix(in srgb, var(--accent) 70%, transparent); }
        .hover\:border-accent-100:hover { border-color: color-mix(in srgb, var(--accent) 20%, transparent); }
        .hover\:border-accent-200:hover { border-color: color-mix(in srgb, var(--accent) 30%, transparent); }
        .hover\:border-accent-300:hover { border-color: color-mix(in srgb, var(--accent) 45%, transparent); }
        .hover\:bg-accent-50:hover { background: color-mix(in srgb, var(--accent) 10%, transparent); }
        .hover\:text-accent:hover { color: var(--accent); }
        .focus\:ring-accent-200:focus { --tw-ring-color: color-mix(in srgb, var(--accent) 30%, transparent); }
        .focus\:border-accent:focus { border-color: var(--accent); }
        .focus\:border-accent-300:focus { border-color: color-mix(in srgb, var(--accent) 45%, transparent); }
        .focus\:border-accent-400:focus { border-color: color-mix(in srgb, var(--accent) 60%, transparent); }
        .peer-checked\:ring-accent-500:checked ~ .peer-checked\:ring-accent-500 { --tw-ring-color: color-mix(in srgb, var(--accent) 70%, transparent); }
        .shadow-accent-200\/50 { box-shadow: 0 4px 6px -1px color-mix(in srgb, var(--accent) 30%, transparent); }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex">
    {{-- Sidebar overlay for mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="flex w-full h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">SI</div>
                <div>
                    <div class="sidebar-brand-text">SIAKAD</div>
                    <div class="sidebar-brand-sub">{{ \App\Enums\CurriculumType::fromSession()->label() }}</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                {{-- Utama --}}
                <div class="sidebar-section">Utama</div>
                <a href="{{ url('/backend/dashboard') }}" class="sidebar-link {{ request()->is('backend/dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>

                {{-- Master Data --}}
                <div class="sidebar-section">Master Data</div>
                @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
                <a href="{{ url('/backend/master/users') }}" class="sidebar-link {{ request()->is('backend/master/users*') ? 'active' : '' }}">
                    <i data-lucide="users"></i> User
                </a>
                @endif
                <a href="{{ url('/backend/master/students') }}" class="sidebar-link {{ request()->is('backend/master/students*') ? 'active' : '' }}">
                    <i data-lucide="graduation-cap"></i> Siswa
                </a>
                @unless(in_array(auth()->user()->role, ['guru']))
                <a href="{{ url('/backend/master/teachers') }}" class="sidebar-link {{ request()->is('backend/master/teachers*') ? 'active' : '' }}">
                    <i data-lucide="briefcase"></i> Guru
                </a>
                @endunless
                <a href="{{ url('/backend/master/classes') }}" class="sidebar-link {{ request()->is('backend/master/classes*') ? 'active' : '' }}">
                    <i data-lucide="school"></i> Kelas
                </a>
                <a href="{{ url('/backend/master/subjects') }}" class="sidebar-link {{ request()->is('backend/master/subjects*') ? 'active' : '' }}">
                    <i data-lucide="book-open"></i> Mapel
                </a>
                <a href="{{ url('/backend/master/academic-setup') }}" class="sidebar-link {{ request()->is('backend/master/academic-setup*') ? 'active' : '' }}">
                    <i data-lucide="calendar-cog"></i> Thn & Semester
                </a>
                <a href="{{ url('/backend/master/class-subject') }}" class="sidebar-link {{ request()->is('backend/master/class-subject*') ? 'active' : '' }}">
                    <i data-lucide="git-branch"></i> Kelas-Mapel
                </a>
                @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
                <a href="{{ url('/backend/settings') }}" class="sidebar-link {{ request()->is('backend/settings*') ? 'active' : '' }}">
                    <i data-lucide="settings"></i> Pengaturan
                </a>
                @endif

                {{-- Akademik --}}
                <div class="sidebar-section">Akademik</div>
                <a href="{{ url('/backend/academic/grades') }}" class="sidebar-link {{ request()->is('backend/academic/grades*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3"></i> Nilai
                </a>
                <a href="{{ url('/backend/academic/reports') }}" class="sidebar-link {{ request()->is('backend/academic/reports*') ? 'active' : '' }}">
                    <i data-lucide="file-text"></i> Rapor
                </a>
                <a href="{{ url('/backend/academic/attendance') }}" class="sidebar-link {{ request()->is('backend/academic/attendance*') ? 'active' : '' }}">
                    <i data-lucide="calendar-check"></i> Presensi
                </a>
                <a href="{{ url('/backend/academic/curriculum') }}" class="sidebar-link {{ request()->is('backend/academic/curriculum*') ? 'active' : '' }}">
                    <i data-lucide="target"></i> Kurikulum
                </a>
                @if(\App\Enums\CurriculumType::fromSession()->supportsP5())
                @unless(in_array(auth()->user()->role, ['guru']))
                <a href="{{ url('/backend/academic/p5') }}" class="sidebar-link {{ request()->is('backend/academic/p5*') ? 'active' : '' }}">
                    <i data-lucide="sparkles"></i> P5
                </a>
                @endunless
                @endif

                {{-- Ujian --}}
                <div class="sidebar-section">Ujian</div>
                <a href="{{ url('/backend/exam/banks') }}" class="sidebar-link {{ request()->is('backend/exam/banks*') ? 'active' : '' }}">
                    <i data-lucide="package"></i> Bank Soal
                </a>
                <a href="{{ url('/backend/exam/questions') }}" class="sidebar-link {{ request()->is('backend/exam/questions*') ? 'active' : '' }}">
                    <i data-lucide="help-circle"></i> Soal
                </a>
                <a href="{{ url('/backend/exam/list') }}" class="sidebar-link {{ request()->is('backend/exam/list*') ? 'active' : '' }}">
                    <i data-lucide="clipboard-list"></i> Ujian
                </a>
                <a href="{{ url('/backend/exam/results') }}" class="sidebar-link {{ request()->is('backend/exam/results*') ? 'active' : '' }}">
                    <i data-lucide="check-circle-2"></i> Hasil
                </a>

                {{-- Keuangan --}}
                @unless(in_array(auth()->user()->role, ['guru']))
                <div class="sidebar-section">Keuangan</div>
                <a href="{{ url('/backend/finance/fee-types') }}" class="sidebar-link {{ request()->is('backend/finance/fee-types*') ? 'active' : '' }}">
                    <i data-lucide="tags"></i> Jenis Biaya
                </a>
                <a href="{{ url('/backend/finance/invoices') }}" class="sidebar-link {{ request()->is('backend/finance/invoices*') ? 'active' : '' }}">
                    <i data-lucide="receipt"></i> Tagihan
                </a>
                <a href="{{ url('/backend/finance/payments') }}" class="sidebar-link {{ request()->is('backend/finance/payments*') ? 'active' : '' }}">
                    <i data-lucide="credit-card"></i> Pembayaran
                </a>
                <a href="{{ url('/backend/finance/reports') }}" class="sidebar-link {{ request()->is('backend/finance/reports*') ? 'active' : '' }}">
                    <i data-lucide="trending-up"></i> Laporan
                </a>
                @endunless
            </nav>

            {{-- Staff & Pegawai --}}
            <div class="sidebar-section">Staff &amp; Pegawai</div>
            @unless(in_array(auth()->user()->role, ['guru']))
            <a href="{{ url('/backend/staff') }}" class="sidebar-link {{ request()->is('backend/staff') ? 'active' : '' }}">
                <i data-lucide="users"></i> Data Pegawai
            </a>
            @endunless
            <a href="{{ url('/backend/staff/attendance') }}" class="sidebar-link {{ request()->is('backend/staff/attendance') ? 'active' : '' }}">
                <i data-lucide="clipboard-check"></i> Grid Absensi
            </a>
            <a href="{{ url('/backend/staff/attendance/recap') }}" class="sidebar-link {{ request()->is('backend/staff/attendance/recap*') ? 'active' : '' }}">
                <i data-lucide="bar-chart-3"></i> Rekap Absensi
            </a>

            {{-- Absensi Manual --}}
            <div class="sidebar-section">Absensi Manual</div>
            <a href="{{ url('/backend/attendance/siswa-manual') }}" class="sidebar-link {{ request()->is('backend/attendance/siswa*') ? 'active' : '' }}">
                <i data-lucide="graduation-cap"></i> Input Siswa
            </a>
            <a href="{{ url('/backend/attendance/pegawai-manual') }}" class="sidebar-link {{ request()->is('backend/attendance/pegawai*') ? 'active' : '' }}">
                <i data-lucide="clipboard-check"></i> Input Pegawai
            </a>

            {{-- Fingerprint --}}
            @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
            <div class="sidebar-section">Sinkron Fingerprint</div>
            <a href="{{ url('/backend/fingerprint') }}" class="sidebar-link {{ request()->is('backend/fingerprint') ? 'active' : '' }}">
                <i data-lucide="fingerprint"></i> Fingerprint
            </a>
            @endif

            {{-- Integrasi Dapodik --}}
            @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
            <div class="sidebar-section">Integrasi Dapodik</div>
            <a href="{{ url('/backend/dapodik') }}" class="sidebar-link {{ request()->is('backend/dapodik') ? 'active' : '' }}">
                <i data-lucide="database"></i> Ekspor Data
            </a>
            <a href="{{ url('/backend/dapodik/mappings') }}" class="sidebar-link {{ request()->is('backend/dapodik/mappings*') ? 'active' : '' }}">
                <i data-lucide="link"></i> Mapping Kode
            </a>
            <a href="{{ url('/backend/dapodik/logs') }}" class="sidebar-link {{ request()->is('backend/dapodik/logs*') ? 'active' : '' }}">
                <i data-lucide="scroll-text"></i> Log Sinkronisasi
            </a>
            @endif

            {{-- Sidebar footer --}}
            <div style="padding:12px;border-top:1px solid #e2e8f0;">
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;">
                    <div class="user-avatar" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="color:#0f172a;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name ?? 'User' }}</div>
                        <div style="color:#64748b;font-size:10px;font-weight:500;">{{ auth()->user()->role ?? 'guest' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Toggle menu">
                        <i data-lucide="menu" style="width:22px;height:22px;"></i>
                    </button>
                    <h1>@yield('page_title', 'Dashboard')</h1>
                </div>
                <div class="topbar-right">
                    <span class="role-badge">{{ auth()->user()->role ?? '' }}</span>
                    @php $cur = \App\Enums\CurriculumType::fromSession(); @endphp
                    <span class="role-badge" style="background:{{ $cur->color() }}15; color:{{ $cur->color() }}; font-weight:600;">
                        {{ $cur->label() }}
                    </span>
                    <div style="position:relative;">
                        <button onclick="toggleThemePanel()" class="theme-btn" id="themeToggleBtn" style="background:var(--accent);width:30px;height:30px;border-radius:8px;border:2px solid #e2e8f0;cursor:pointer;" title="Ganti Tema"></button>
                        <div id="themePanel">
                            <h4>Pilih Tema Warna</h4>
                            <div class="theme-grid" id="themeGrid"></div>
                        </div>
                    </div>
                    <form method="POST" action="{{ url('/backend/logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i data-lucide="log-out" style="width:16px;height:16px;display:inline;vertical-align:-3px;"></i> Keluar
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Confirm Modal --}}
    <div id="confirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm" style="display:none;">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6" onclick="event.stopPropagation()">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-800" id="confirmTitle">Konfirmasi</h3>
                    <p class="text-sm text-slate-500 mt-1" id="confirmMessage">Apakah Anda yakin?</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button onclick="closeConfirm()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                <button id="confirmOkBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">Ya, Hapus</button>
            </div>
        </div>
    </div>

    {{-- Success Toast --}}
    <div id="successToast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 btn-accent text-white px-5 py-3 rounded-xl shadow-lg" style="display:none;">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium" id="toastMessage">Berhasil!</span>
        <button onclick="this.parentElement.classList.add('hidden');this.parentElement.style.display='none'" class="ml-2 text-white/70 hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Error Toast --}}
    <div id="errorToast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 bg-red-600 text-white px-5 py-3 rounded-xl shadow-lg shadow-red-200/50" style="display:none;">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium" id="errorToastMessage">Gagal!</span>
        <button onclick="this.parentElement.classList.add('hidden');this.parentElement.style.display='none'" class="ml-2 text-white/70 hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <script>
        // ─── Sidebar ─────────────────────────────────────────────
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
        document.querySelectorAll('.sidebar-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        // ─── Per Page ────────────────────────────────────────────
        function changePerPage(val) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        // ─── Confirm Modal ───────────────────────────────────────
        let confirmCallback = null;

        function showConfirm(message, title, okText, callback) {
            document.getElementById('confirmTitle').textContent = title || 'Konfirmasi';
            document.getElementById('confirmMessage').textContent = message || 'Apakah Anda yakin?';
            document.getElementById('confirmOkBtn').textContent = okText || 'Ya, Hapus';
            confirmCallback = callback;
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        function closeConfirm() {
            const modal = document.getElementById('confirmModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            confirmCallback = null;
        }

        document.getElementById('confirmOkBtn').addEventListener('click', function() {
            if (confirmCallback) confirmCallback();
            closeConfirm();
        });

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeConfirm();
        });

        // ─── Toast ───────────────────────────────────────────────
        function showToast(message, type) {
            const id = type === 'error' ? 'errorToast' : 'successToast';
            const msgId = type === 'error' ? 'errorToastMessage' : 'toastMessage';
            const toast = document.getElementById(id);
            document.getElementById(msgId).textContent = message;
            toast.classList.remove('hidden');
            toast.style.display = 'flex';
            setTimeout(function() {
                toast.classList.add('hidden');
                toast.style.display = 'none';
            }, 4000);
        }

        // Auto-show toast from session flash
        document.addEventListener('DOMContentLoaded', function() {
            initThemePicker();
            @if(session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif
            @if(session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
        });

        // ─── Theme Picker ────────────────────────────────────────
        const themes = [
            { name: 'Emerald', accent: '#059669', light: '#34d399', bg: '#ecfdf5', desc: 'Zamrud' },
            { name: 'Slate', accent: '#475569', light: '#64748b', bg: '#f1f5f9', desc: 'Abu-abu' },
            { name: 'Orange', accent: '#ea580c', light: '#fb923c', bg: '#fff7ed', desc: 'Oranye' },
            { name: 'Rose', accent: '#e11d48', light: '#fb7185', bg: '#fff1f2', desc: 'Mawar' },
            { name: 'Sky-Alt', accent: '#0ea5e9', light: '#7dd3fc', bg: '#f0f9ff', desc: 'Biru Muda' },
        ];

        function initThemePicker() {
            const grid = document.getElementById('themeGrid');
            const saved = localStorage.getItem('siakad_theme') || '0';

            themes.forEach(function(t, i) {
                const wrapper = document.createElement('div');
                const btn = document.createElement('button');
                btn.className = 'theme-btn' + (i == saved ? ' active' : '');
                btn.style.background = t.accent;
                btn.dataset.index = i;
                btn.onclick = function() { setTheme(i); };
                wrapper.appendChild(btn);
                const label = document.createElement('div');
                label.className = 'theme-label';
                label.textContent = t.desc;
                wrapper.appendChild(label);
                grid.appendChild(wrapper);
            });

            applyTheme(parseInt(saved));
        }

        function setTheme(index) {
            localStorage.setItem('siakad_theme', index);
            document.querySelectorAll('#themeGrid .theme-btn').forEach(function(b) {
                b.classList.toggle('active', parseInt(b.dataset.index) === index);
            });
            applyTheme(index);
            closeThemePanel();
            showToast('Tema: ' + themes[index].desc, 'success');
        }

        function applyTheme(index) {
            const t = themes[index];
            const root = document.documentElement;
            root.style.setProperty('--accent', t.accent);
            root.style.setProperty('--accent-light', t.light);
            document.getElementById('themeToggleBtn').style.background = t.accent;
            // Update success toast to use accent color
            const toast = document.getElementById('successToast');
            if (toast) {
                toast.style.background = t.accent;
                toast.style.boxShadow = '0 10px 15px -3px ' + t.accent + '40';
            }
        }

        function toggleThemePanel() {
            document.getElementById('themePanel').classList.toggle('show');
        }
        function closeThemePanel() {
            document.getElementById('themePanel').classList.remove('show');
        }
        document.addEventListener('click', function(e) {
            const panel = document.getElementById('themePanel');
            if (panel && panel.classList.contains('show') && !e.target.closest('#themePanel') && !e.target.closest('#themeToggleBtn')) {
                closeThemePanel();
            }
        });

        // ─── Lucide ──────────────────────────────────────────────
        try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
    </script>
    @stack('scripts')
</body>
</html>
