<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan profil lengkap siswa.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::with([
            'class:id,code,tingkat,wali_kelas_id',
            'class.waliKelas:id,name,phone,photo',
            'user:id,name,email,phone,photo',
        ])
            ->where('user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
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
                'akun' => [
                    'email' => $student->user?->email,
                    'phone' => $student->user?->phone,
                    'photo' => $student->user?->photo,
                ],
            ],
        ]);
    }

    /**
     * Update profil siswa.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            // Data siswa (tabel students)
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            // Data akun (tabel users)
            'email' => ['nullable','email', Rule::unique('users')->ignore($user->id)],
            'phone_akun' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update tabel students
        $studentData = array_filter([
            'tempat_lahir' => $validated['tempat_lahir'] ?? $student->tempat_lahir,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? $student->tanggal_lahir,
            'agama' => $validated['agama'] ?? $student->agama,
            'alamat' => $validated['alamat'] ?? $student->alamat,
            'phone' => $validated['phone'] ?? $student->phone,
        ], fn($v) => $v !== null);

        $student->update($studentData);

        // Update tabel users
        $userData = [];
        if ($request->has('email')) {
            $userData['email'] = $validated['email'];
        }
        if ($request->has('phone_akun')) {
            $userData['phone'] = $validated['phone_akun'];
        }

        // Upload foto
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/siswa', 'public');
            // Hapus foto lama jika ada
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $userData['photo'] = $path;
        }

        if (!empty($userData)) {
            $user->update($userData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'student' => [
                    'tempat_lahir' => $student->fresh()->tempat_lahir,
                    'tanggal_lahir' => $student->fresh()->tanggal_lahir?->format('Y-m-d'),
                    'agama' => $student->fresh()->agama,
                    'alamat' => $student->fresh()->alamat,
                    'phone' => $student->fresh()->phone,
                ],
                'akun' => [
                    'email' => $user->fresh()->email,
                    'phone' => $user->fresh()->phone,
                    'photo' => $user->fresh()->photo,
                ],
            ],
        ]);
    }
}
