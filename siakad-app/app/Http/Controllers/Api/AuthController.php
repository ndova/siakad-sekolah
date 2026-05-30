<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login & dapatkan token Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string|max:200',
            'password' => 'required',
        ]);

        $identifier = $request->identifier;

        // Coba login via email dulu
        $user = User::where('email', $identifier)->first();

        // Jika tidak ditemukan, coba login via NIS (untuk role siswa)
        if (!$user) {
            $student = Student::where('nis', $identifier)->first();
            if ($student) {
                $user = $student->user;
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Email/NIS atau password salah.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'school_id' => $user->school_id,
                ],
            ],
        ]);
    }

    /**
     * Register user baru (hanya untuk siswa & ortu via portal).
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'email' => ['required','email', Rule::unique('users')],
            'password' => 'required|string|min:6',
            'role' => 'required|in:siswa,orang_tua',
            'school_id' => 'required_if:role,siswa|exists:schools,id',
            // Data profil siswa (opsional untuk ortu, dipakai jika role=siswa)
            'nis' => 'required_if:role,siswa|string|max:20',
            'nisn' => ['required_if:role,siswa','string','max:10', Rule::unique('students','nisn')->where('status','aktif')->whereNull('deleted_at')],
            'jk' => 'required_if:role,siswa|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'class_id' => 'nullable|exists:classes,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'school_id' => $request->school_id,
            'is_active' => true,
        ]);

        // Upload foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos/siswa', 'public');
            $user->update(['photo' => $photoPath]);
        }

        // Jika role siswa, buat data Student + update phone di users
        $studentData = null;
        if ($request->role === 'siswa') {
            $student = Student::create([
                'user_id' => $user->id,
                'school_id' => $user->school_id,
                'class_id' => $request->class_id,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->name,
                'jk' => $request->jk,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'phone' => $request->phone,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
            ]);

            $student->load(['class.waliKelas:id,name,phone,photo']);
            $studentData = [
                'id' => $student->id,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'jk' => $student->jk,
                'tempat_lahir' => $student->tempat_lahir,
                'tanggal_lahir' => $student->tanggal_lahir?->format('Y-m-d'),
                'agama' => $student->agama,
                'alamat' => $student->alamat,
                'phone' => $student->phone,
                'kelas' => $student->class?->code,
                'tingkat' => $student->class?->tingkat,
                'wali_kelas' => $student->class?->waliKelas ? [
                    'id' => $student->class->waliKelas->id,
                    'name' => $student->class->waliKelas->name,
                    'phone' => $student->class->waliKelas->phone,
                    'photo' => $student->class->waliKelas->photo,
                ] : null,
            ];
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'photo' => $user->photo,
                ],
                'student' => $studentData,
            ],
        ], 201);
    }

    /**
     * Logout — hapus current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    /**
     * Get current authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('school');

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'photo' => $user->photo,
            'school' => $user->school,
        ];

        // Jika role siswa, sertakan data profil siswa
        if ($user->role === 'siswa') {
            $student = Student::with([
                'class:id,code,tingkat,wali_kelas_id',
                'class.waliKelas:id,name,phone,photo',
            ])
                ->where('user_id', $user->id)
                ->first();

            if ($student) {
                $data['student'] = [
                    'id' => $student->id,
                    'nama_lengkap' => $student->nama_lengkap,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'jk' => $student->jk,
                    'tempat_lahir' => $student->tempat_lahir,
                    'tanggal_lahir' => $student->tanggal_lahir?->format('Y-m-d'),
                    'agama' => $student->agama,
                    'alamat' => $student->alamat,
                    'phone' => $student->phone,
                    'nama_ayah' => $student->nama_ayah,
                    'nama_ibu' => $student->nama_ibu,
                    'status' => $student->status,
                    'tanggal_masuk' => $student->tanggal_masuk?->format('Y-m-d'),
                    'kelas' => $student->class?->code,
                    'tingkat' => $student->class?->tingkat,
                    'wali_kelas' => $student->class?->waliKelas ? [
                        'id' => $student->class->waliKelas->id,
                        'name' => $student->class->waliKelas->name,
                        'phone' => $student->class->waliKelas->phone,
                        'photo' => $student->class->waliKelas->photo,
                    ] : null,
                ];
            }
        }

        return response()->json(['data' => $data]);
    }
}
