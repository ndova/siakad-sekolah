@php
use App\Services\SchoolService;
$school = SchoolService::get();
$primaryColor = $school?->primary_color ?? '#059669';
@endphp

<x-auth-split-layout
    :portalTitle="'SIAKAD Backend'"
    :schoolName="$school?->name ?? 'SIAKAD'"
    :schoolNpsn="$school?->npsn"
    :logoUrl="$school?->logo ? asset('storage/'.$school->logo) : null"
    :landingImageUrl="$school?->landing_image ? asset('storage/'.$school->landing_image) : null"
    :welcomeText="'Panel Administrasi'"
    :tagline="($school?->name ?? 'SIAKAD') . ' — Sistem Informasi Akademik Terpadu'"
    :footerText="$school?->footer_text ?? '© ' . date('Y') . ' ' . ($school?->name ?? 'SIAKAD')"
    :primaryColor="$primaryColor"
    :primaryLightColor="$school?->primary_color_light ?? '#34d399'"
    :formTitle="'Login Backend'"
    :formSubtitle="'Masuk sebagai admin, guru, atau staff'"
>
    <form id="loginForm" class="space-y-5" onsubmit="return false;">
        @csrf
        <div id="loginError" class="hidden px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-red-500 text-xs font-medium text-center"></div>

        <div>
            <label for="emailInput" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Email</label>
            <div class="relative">
                <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"></i>
                <input id="emailInput" type="email" name="email" required autocomplete="email"
                    placeholder="admin@siakad.test"
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:ring-2 ring-primary focus:bg-white transition">
            </div>
        </div>

        <div>
            <label for="passwordInput" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Password</label>
            <div class="relative">
                <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"></i>
                <input id="passwordInput" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full pl-10 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:ring-2 ring-primary focus:bg-white transition">
                <button type="button" id="togglePass" tabindex="-1"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition cursor-pointer bg-transparent border-none p-0">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="showPassword" class="w-4 h-4 rounded border-slate-300 accent-emerald-600">
            <label for="showPassword" class="text-xs text-slate-400 cursor-pointer select-none">Tampilkan Password</label>
        </div>

        <button id="loginBtn" type="button"
            class="w-full py-3 text-white font-semibold rounded-xl text-sm transition-all duration-200 btn-primary shadow-lg"
            style="box-shadow:0 4px 14px color-mix(in srgb, var(--primary) 35%, transparent);">
            Masuk
        </button>
    </form>

    <p class="mt-4 text-center text-xs text-slate-300">
        admin@siakad.test / password123
    </p>

    <script>
    (function() {
        'use strict';
        lucide.createIcons();

        const btn     = document.getElementById('loginBtn');
        const errEl   = document.getElementById('loginError');
        const emailEl = document.getElementById('emailInput');
        const passEl  = document.getElementById('passwordInput');
        const toggle  = document.getElementById('togglePass');
        const showPw  = document.getElementById('showPassword');

        toggle.addEventListener('click', function() {
            const isPass = passEl.type === 'password';
            passEl.type = isPass ? 'text' : 'password';
            this.innerHTML = isPass ? '<i data-lucide="eye" class="w-4 h-4"></i>' : '<i data-lucide="eye-off" class="w-4 h-4"></i>';
            showPw.checked = !isPass;
            lucide.createIcons();
        });

        showPw.addEventListener('change', function() {
            passEl.type = this.checked ? 'text' : 'password';
            toggle.innerHTML = this.checked ? '<i data-lucide="eye" class="w-4 h-4"></i>' : '<i data-lucide="eye-off" class="w-4 h-4"></i>';
            lucide.createIcons();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.activeElement && document.activeElement.closest('#loginForm')) {
                e.preventDefault(); doLogin();
            }
        });

        btn.addEventListener('click', doLogin);

        async function doLogin() {
            const email = emailEl.value.trim();
            const password = passEl.value;

            if (!email || !password) {
                showError('Email dan password harus diisi.');
                return;
            }

            setLoading(true);

            try {
                const csrfToken = document.querySelector('input[name="_token"]').value;
                const res = await fetch('/backend/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();

                if (res.ok && data.redirect) {
                    window.location.replace(data.redirect);
                } else {
                    showError(data.message || 'Email atau password salah.');
                }
            } catch (err) {
                console.error('Login error:', err);
                showError('Gagal terhubung ke server. Coba lagi.');
            } finally {
                setLoading(false);
            }
        }

        function setLoading(loading) {
            btn.disabled = loading;
            btn.innerHTML = loading
                ? '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>'
                : 'Masuk';
            btn.classList.toggle('opacity-70', loading);
        }

        function showError(msg) {
            errEl.textContent = msg;
            errEl.classList.remove('hidden');
        }
    })();
    </script>
</x-auth-split-layout>

