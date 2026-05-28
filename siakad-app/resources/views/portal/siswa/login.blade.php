@php
use App\Services\SchoolService;
$school = SchoolService::get();
$primaryColor = $school?->primary_color ?? '#2563eb';
$footerLinkHtml = 'Orang tua? <a href="' . route('portal.ortu.login') . '" class="font-medium hover:underline" style="color:' . $primaryColor . '">Login di sini</a>';
@endphp

<x-auth-split-layout
    :portalTitle="$school?->portal_title ?? 'Portal Siswa'"
    :schoolName="$school?->name ?? 'SIAKAD'"
    :schoolNpsn="$school?->npsn"
    :logoUrl="$school?->logo ? asset('storage/'.$school->logo) : null"
    :landingImageUrl="$school?->landing_image ? asset('storage/'.$school->landing_image) : null"
    :welcomeText="$school?->welcome_text ?? 'Selamat Datang! di Sistem Informasi Akademik'"
    :tagline="$school?->tagline ?? 'Akses nilai, presensi, ujian, dan pembayaran dalam satu portal.'"
    :footerText="$school?->footer_text ?? '© ' . date('Y') . ' ' . ($school?->name ?? 'SIAKAD')"
    :primaryColor="$primaryColor"
    :primaryLightColor="$school?->primary_color_light ?? '#3b82f6'"
    :formTitle="'Masuk ke Portal Siswa'"
    :formSubtitle="'Gunakan NIS atau email untuk login'"
    :footerLink="$footerLinkHtml"
>

    <form id="loginForm" class="space-y-5" onsubmit="return false;">
        <div id="errorBox" class="hidden px-4 py-3 bg-red-50 border border-red-100 rounded-xl text-red-500 text-xs font-medium text-center"></div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Email / NIS</label>
            <div class="relative">
                <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"></i>
                <input type="text" id="email" autocomplete="username"
                    placeholder="andi.pratama@siakad.test"
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
            <input type="checkbox" id="showPassword" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
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
            const identifier = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errEl = document.getElementById('errorBox');
            const btn = document.getElementById('loginBtn');

            if (!identifier || !password) {
                errEl.textContent = 'Email/NIS dan password harus diisi.';
                errEl.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Memproses...';
            errEl.classList.add('hidden');

            try {
                const res = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ identifier, password }),
                });

                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.message || 'Login gagal.');
                }

                if (data.data?.user?.role !== 'siswa') {
                    throw new Error('Akun ini bukan akun siswa.');
                }

                localStorage.setItem('sia_token', data.data.token);
                localStorage.setItem('sia_user', JSON.stringify(data.data.user));
                window.location.href = '/portal/siswa/dashboard';
            } catch (err) {
                errEl.textContent = err.message;
                errEl.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Masuk';
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

