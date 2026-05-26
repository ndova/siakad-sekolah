<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Role;
use App\Enums\CurriculumType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm()
    {
        return view('backend.login');
    }

    /**
     * Proses login session-based untuk backend panel.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            // Jika request AJAX/JSON (dari JS fetch)
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email atau password salah.'], 422);
            }
            return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->only('email'));
        }

        if (!$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
            }
            return back()->withErrors(['email' => 'Akun dinonaktifkan.'])->withInput($request->only('email'));
        }

        // Cek role — hanya backend roles yang boleh masuk panel
        $backendRoles = Role::internalRoles();
        if (!in_array($user->role, $backendRoles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Role tidak memiliki akses panel backend.'], 403);
            }
            return back()->withErrors(['email' => 'Role tidak memiliki akses panel backend.'])->withInput($request->only('email'));
        }

        // Login dengan session guard (web)
        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        // Tentukan halaman setelah login berdasarkan kurikulum yang tersedia
        $nextUrl = $this->resolvePostLoginUrl();

        // Jika request JSON (AJAX dari JS fetch), return redirect URL
        if ($request->expectsJson()) {
            return response()->json([
                'message'   => 'Login berhasil',
                'redirect'  => url($nextUrl),
                'user'      => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ]);
        }

        return redirect()->intended($nextUrl);
    }

    /**
     * Tentukan URL setelah login berdasarkan ketersediaan kurikulum.
     */
    protected function resolvePostLoginUrl(): string
    {
        // Jika session sudah punya curriculum_type yang valid, langsung dashboard
        if (session()->has('curriculum_type') && CurriculumType::isSessionValid()) {
            return '/backend/dashboard';
        }

        // Jika session curriculum tidak valid (admin menonaktifkan kurikulum yg dipilih)
        if (session()->has('curriculum_type') && !CurriculumType::isSessionValid()) {
            session()->forget('curriculum_type');
        }

        // Cek: hanya satu kurikulum aktif?
        $single = CurriculumType::autoSelectIfSingle();
        if ($single !== null) {
            session(['curriculum_type' => $single->value]);
            return '/backend/dashboard';
        }

        // Lebih dari satu → user harus pilih
        return '/backend/pilih-kurikulum';
    }

    /**
     * Tampilkan halaman pilih kurikulum (interstitial setelah login).
     */
    public function selectCurriculum()
    {
        if (!Auth::check()) {
            return redirect('/backend/login');
        }

        // Auto-select jika hanya satu kurikulum tersedia
        $single = CurriculumType::autoSelectIfSingle();
        if ($single !== null) {
            session(['curriculum_type' => $single->value]);
            return redirect('/backend/dashboard');
        }

        // Jika sudah pilih kurikulum yang valid, langsung ke dashboard
        if (session()->has('curriculum_type') && CurriculumType::isSessionValid()) {
            return redirect('/backend/dashboard');
        }

        // Reset invalid session curriculum
        session()->forget('curriculum_type');

        return view('backend.curriculum-select');
    }

    /**
     * Simpan pilihan kurikulum ke session.
     */
    public function storeCurriculum(Request $request)
    {
        $validated = $request->validate([
            'curriculum_type' => 'required|in:kurmer,k13',
        ]);

        $chosen = CurriculumType::tryFrom($validated['curriculum_type']);
        if (!$chosen || !in_array($chosen, CurriculumType::available(), true)) {
            return back()->withErrors(['curriculum_type' => 'Kurikulum yang dipilih tidak tersedia. Hubungi administrator.']);
        }

        session(['curriculum_type' => $chosen->value]);

        return redirect('/backend/dashboard');
    }

    /**
     * Logout — hapus session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/backend/login');
    }
}
