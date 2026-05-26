<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal SIAKAD')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #059669;
            --accent-light: #34d399;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Sidebar */
        #sidebar {
            width: 260px;
            min-width: 260px;
            background: #fff;
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
        .sidebar-link:hover { background: #f1f5f9; color: #0f172a; }
        .sidebar-link.active {
            background: color-mix(in srgb, var(--accent) 15%, transparent);
            color: var(--accent);
            font-weight: 600;
        }
        .sidebar-link svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.6; }
        .sidebar-link.active svg { opacity: 1; color: var(--accent); }
        .sidebar-link:hover svg { opacity: 0.8; }
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid #e2e8f0;
        }
        .sidebar-footer-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
        }
        .sidebar-footer-avatar {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 11px;
            flex-shrink: 0;
        }
        .sidebar-footer-name {
            color: #0f172a;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-footer-role {
            color: #64748b;
            font-size: 10px;
            font-weight: 500;
        }

        /* Mobile Sidebar */
        @media (max-width: 1024px) {
            #sidebar { position: fixed; left: -280px; top: 0; bottom: 0; transition: left 0.3s ease; z-index: 50; }
            #sidebar.open { left: 0; box-shadow: 4px 0 25px rgba(0,0,0,0.1); }
            #sidebarOverlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }
            #sidebarOverlay.show { display: block; }
        }

        .card { @apply bg-white rounded-2xl border border-slate-100 shadow-sm; }

        /* Hamburger (visible only on mobile) */
        @media (max-width: 1024px) {
            #hamburgerBtn { display: flex !important; align-items: center; justify-content: center; }
        }

        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { filter: brightness(1.1); }
        .btn-accent-soft { background: color-mix(in srgb, var(--accent) 15%, transparent); color: var(--accent); }
        .text-accent { color: var(--accent); }
        .bg-accent-50 { background: color-mix(in srgb, var(--accent) 10%, transparent); }
        .bg-accent-100 { background: color-mix(in srgb, var(--accent) 20%, transparent); }
        .border-accent { border-color: var(--accent); }
        .focus\:ring-accent-200:focus { --tw-ring-color: color-mix(in srgb, var(--accent) 30%, transparent); }
        .hover\:bg-accent-50:hover { background: color-mix(in srgb, var(--accent) 10%, transparent); }
        .hover\:text-accent:hover { color: var(--accent); }
        .ring-accent-200 { --tw-ring-color: color-mix(in srgb, var(--accent) 30%, transparent); }
        .theme-btn {
            width: 28px; height: 28px;
            border-radius: 8px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
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
    </style>
</head>
<body class="min-h-screen flex" style="background:#f1f5f9;">

    <div class="flex w-full h-screen overflow-hidden">

    {{-- Mobile Sidebar Overlay --}}
    <div id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">SI</div>
            <div>
                <div class="sidebar-brand-text">SIAKAD</div>
                <div class="sidebar-brand-sub">Sistem Akademik</div>
            </div>
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            @yield('sidebar-nav')
        </nav>

        <div class="sidebar-footer" id="sidebarUser">
            <div class="sidebar-footer-inner">
                <div class="sidebar-footer-avatar" id="sidebarAvatar">?</div>
                <div style="flex:1;min-width:0;">
                    <div class="sidebar-footer-name" id="sidebarName">Loading...</div>
                    <div class="sidebar-footer-role" id="sidebarRole"></div>
                </div>
            </div>
            <button onclick="doLogout()" style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 12px;font-size:13px;color:#94a3b8;background:none;border:none;cursor:pointer;border-radius:8px;margin-top:4px;font-weight:500;font-family:inherit;transition:all 0.15s;" onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2'" onmouseout="this.style.color='#94a3b8';this.style.background='none'">
                <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
            </button>
        </div>
    </aside>

    {{-- Main Content --}}
    <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
        {{-- Topbar --}}
        <header style="display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:#fff;border-bottom:1px solid #e2e8f0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <button onclick="toggleSidebar()" aria-label="Toggle menu" style="display:none;background:none;border:none;cursor:pointer;color:#64748b;padding:4px;" id="hamburgerBtn">
                    <i data-lucide="menu" style="width:22px;height:22px;"></i>
                </button>
                <h1 style="font-size:18px;font-weight:600;color:#0f172a;letter-spacing:-0.3px;">@yield('page_title', 'Portal')</h1>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <button style="position:relative;padding:8px;border-radius:8px;background:none;border:none;cursor:pointer;color:#64748b;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">
                    <i data-lucide="bell" style="width:20px;height:20px;"></i>
                    <span style="position:absolute;top:6px;right:6px;width:7px;height:7px;background:#ef4444;border-radius:50%;" id="notifDot"></span>
                </button>
                <div style="position:relative;">
                    <button onclick="toggleThemePanel()" class="theme-btn" id="themeToggleBtn" style="background:var(--accent);width:30px;height:30px;border-radius:8px;border:2px solid #e2e8f0;cursor:pointer;" title="Ganti Tema"></button>
                    <div id="themePanel">
                        <h4>Pilih Tema Warna</h4>
                        <div class="theme-grid" id="themeGrid"></div>
                    </div>
                </div>
                <div class="sidebar-footer-avatar" id="topAvatar">?</div>
            </div>
        </header>

        {{-- Content --}}
        <main style="flex:1;overflow-y:auto;padding:24px;">
            @yield('content')
        </main>
    </div>

    </div>{{-- close flex wrapper --}}

    {{-- Scripts --}}
    <script>
        const API_BASE = '/api/v1';
        window.currentUser = null;
        window.currentToken = localStorage.getItem('sia_token');

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
                if (window.innerWidth <= 1024) closeSidebar();
            });
        });

        async function apiFetch(url, opts = {}) {
            const token = localStorage.getItem('sia_token');
            const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', ...(opts.headers || {}) };
            if (token) headers['Authorization'] = `Bearer ${token}`;
            const res = await fetch(url, { ...opts, headers });
            if (res.status === 401) { localStorage.clear(); window.location.href = getLoginUrl(); return null; }
            if (!res.ok) { const err = await res.json().catch(() => ({})); throw new Error(err.message || 'Request failed'); }
            return res.json();
        }

        function getLoginUrl() {
            return window.location.pathname.includes('/ortu') ? '/portal/ortu/login' : '/portal/siswa/login';
        }

        function formatRupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

        async function loadSidebarUser() {
            if (!currentToken) { window.location.href = getLoginUrl(); return; }
            try {
                const data = await apiFetch(`${API_BASE}/auth/me`);
                currentUser = data.data;
                const name = currentUser.name || 'User';
                document.getElementById('sidebarName').textContent = name;
                document.getElementById('sidebarRole').textContent = currentUser.role === 'siswa' ? 'Siswa' : 'Orang Tua';
                document.getElementById('sidebarAvatar').textContent = name.charAt(0).toUpperCase();
                document.getElementById('topAvatar').textContent = name.charAt(0).toUpperCase();
            } catch (e) { localStorage.clear(); window.location.href = getLoginUrl(); }
        }

        async function doLogout() {
            try { await apiFetch(`${API_BASE}/auth/logout`, { method: 'POST' }); } catch (e) {}
            localStorage.clear();
            window.location.href = getLoginUrl();
        }

        // ─── Theme Picker ────────────────────────────────────────
        const themes = [
            { name: 'Emerald', accent: '#059669', light: '#34d399', desc: 'Zamrud' },
            { name: 'Slate', accent: '#475569', light: '#64748b', desc: 'Abu-abu' },
            { name: 'Orange', accent: '#ea580c', light: '#fb923c', desc: 'Oranye' },
            { name: 'Rose', accent: '#e11d48', light: '#fb7185', desc: 'Mawar' },
            { name: 'Sky-Alt', accent: '#0ea5e9', light: '#7dd3fc', desc: 'Biru Muda' },
        ];

        function initThemePicker() {
            const grid = document.getElementById('themeGrid');
            if (!grid) return;
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
        }

        function applyTheme(index) {
            const t = themes[index];
            const root = document.documentElement;
            root.style.setProperty('--accent', t.accent);
            root.style.setProperty('--accent-light', t.light);
            const toggleBtn = document.getElementById('themeToggleBtn');
            if (toggleBtn) toggleBtn.style.background = t.accent;
        }

        function toggleThemePanel() {
            const panel = document.getElementById('themePanel');
            if (panel) panel.classList.toggle('show');
        }
        function closeThemePanel() {
            const panel = document.getElementById('themePanel');
            if (panel) panel.classList.remove('show');
        }
        document.addEventListener('click', function(e) {
            const panel = document.getElementById('themePanel');
            if (panel && panel.classList.contains('show') && !e.target.closest('#themePanel') && !e.target.closest('#themeToggleBtn')) {
                closeThemePanel();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            initThemePicker();
            if (currentToken) loadSidebarUser();
        });

        // ─── Toast Notification ─────────────────────────────────
        let toastTimer = null;
        function showToast(message, type) {
            const toast = document.getElementById('portalToast');
            const msgEl = document.getElementById('portalToastMsg');
            if (!toast || !msgEl) return;
            // Clear previous timer
            if (toastTimer) clearTimeout(toastTimer);
            // Set style based on type
            toast.classList.remove('bg-emerald-600', 'bg-red-600', 'bg-amber-500');
            if (type === 'error') {
                toast.classList.add('bg-red-600');
                msgEl.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 inline mr-1.5"></i>' + message;
            } else if (type === 'warning') {
                toast.classList.add('bg-amber-500');
                msgEl.innerHTML = '<i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1.5"></i>' + message;
            } else {
                toast.classList.add('bg-emerald-600');
                msgEl.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4 inline mr-1.5"></i>' + message;
            }
            toast.classList.remove('hidden');
            toast.style.display = 'flex';
            lucide.createIcons();
            toastTimer = setTimeout(function() {
                toast.classList.add('hidden');
                toast.style.display = 'none';
            }, 4000);
        }
    </script>
    {{-- Toast --}}
    <div id="portalToast" class="hidden fixed bottom-6 right-6 z-50 items-center gap-2 text-white px-5 py-3 rounded-xl shadow-lg" style="display:none;">
        <span class="text-sm font-medium" id="portalToastMsg"></span>
    </div>
    @yield('scripts')
</body>
</html>
