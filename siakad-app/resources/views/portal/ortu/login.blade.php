@php
use App\Services\SchoolService;
$school = SchoolService::get();
$primaryColor = $school?->primary_color ?? '#7c3aed';
$footerLinkHtml = 'Siswa? <a href="' . route('portal.siswa.login') . '" class="font-medium hover:underline" style="color:' . $primaryColor . '">Login di sini</a>';
@endphp

<x-auth-split-layout
    :portalTitle="$school?->portal_title ?? 'Portal Orang Tua'"
    :schoolName="$school?->name ?? 'SIAKAD'"
    :schoolNpsn="$school?->npsn"
    :logoUrl="$school?->logo ? asset('storage/'.$school->logo) : null"
    :landingImageUrl="$school?->landing_image ? asset('storage/'.$school->landing_image) : null"
    :welcomeText="$school?->welcome_text ?? 'Selamat Datang! di Sistem Informasi Akademik'"
    :tagline="$school?->tagline ?? 'Pantau perkembangan akademik anak Anda dalam satu portal.'"
    :footerText="$school?->footer_text ?? '© ' . date('Y') . ' ' . ($school?->name ?? 'SIAKAD')"
    :primaryColor="$primaryColor"
    :primaryLightColor="$school?->primary_color_light ?? '#a78bfa'"
    :formTitle="'Masuk ke Portal Orang Tua'"
    :formSubtitle="'Pantau perkembangan akademik anak Anda'"
    :footerLink="$footerLinkHtml"
>
    <form id="loginForm" class="space-y-5" onsubmit="return false;">
        <div id="errorBox" class="hidden px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-red-500 text-xs font-medium text-center"></div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Email</label>
            <div class="relative">
                <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"></i>
                <input type="text" id="email" autocomplete="username"
                    placeholder="ortu.andi@siakad.test"
                    class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:ring-2 ring-primary focus:bg-white transition">
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Kata Sandi</label>
            <div class="relative">
                <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"></i>
                <input type="password" id="password" autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full pl-10 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:ring-2 ring-primary focus:bg-white transition">
                <button type="button" id="togglePass" tabindex="-1"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition cursor-pointer bg-transparent border-none p-0">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="showPassword" class="w-4 h-4 rounded border-slate-300 accent-purple-600">
            <label for="showPassword" class="text-xs text-slate-400 cursor-pointer select-none">Tampilkan Password</label>
        </div>

        <button type="button" id="loginBtn" onclick="doLogin()"
            class="w-full py-3 text-white font-semibold rounded-xl text-sm transition-all duration-200 btn-primary shadow-lg"
            style="box-shadow:0 4px 14px color-mix(in srgb, var(--primary) 35%, transparent);">
            Masuk
        </button>
    </form>

    <script>
        const togglePass = document.getElementById('togglePass');
        const passwordInput = document.getElementById('password');
        const showPassCheck = document.getElementById('showPassword');

        togglePass.addEventListener('click', () => {
            const isPass = passwordInput.type === 'password';
            passwordInput.type = isPass ? 'text' : 'password';
            togglePass.innerHTML = isPass ? '<i data-lucide="eye" class="w-4 h-4"></i>' : '<i data-lucide="eye-off" class="w-4 h-4"></i>';
            lucide.createIcons();
        });

        showPassCheck.addEventListener('change', () => {
            passwordInput.type = showPassCheck.checked ? 'text' : 'password';
            togglePass.innerHTML = showPassCheck.checked ? '<i data-lucide="eye" class="w-4 h-4"></i>' : '<i data-lucide="eye-off" class="w-4 h-4"></i>';
            lucide.createIcons();
        });

        async function doLogin() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errEl = document.getElementById('errorBox');
            const btn = document.getElementById('loginBtn');

            if (!email || !password) {
                errEl.textContent = 'Email dan password harus diisi.';
                errEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true; btn.textContent = 'Memproses...'; errEl.classList.add('hidden');
            try {
                const res = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const data = await res.json();
                if (res.ok && data.data?.token) {
                    if (data.data.user?.role !== 'orang_tua') {
                        throw new Error('Akun ini bukan akun orang tua.');
                    }
                    localStorage.setItem('sia_token', data.data.token);
                    localStorage.setItem('sia_user', JSON.stringify(data.data.user));
                    window.location.href = '/portal/ortu/dashboard';
                } else {
                    throw new Error(data.message || 'Login gagal.');
                }
            } catch (e) {
                errEl.textContent = e.message; errEl.classList.remove('hidden');
            } finally {
                btn.disabled = false; btn.textContent = 'Masuk';
            }
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && document.activeElement && document.activeElement.closest('#loginForm')) {
                doLogin();
            }
        });

        lucide.createIcons();
    </script>
</x-auth-split-layout>

