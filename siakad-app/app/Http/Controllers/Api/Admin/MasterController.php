<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    /**
     * Manajemen user (CRUD list)
     */
    public function users(Request $request): JsonResponse
    {
        $users = User::with('school:id,name')
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('nip', 'like', "%{$request->search}%");
                });
            })
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $users->through(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'nip' => $u->nip,
                'phone' => $u->phone,
                'is_active' => $u->is_active,
                'last_login_at' => $u->last_login_at?->toISOString(),
                'school' => $u->school?->name,
                'created_at' => $u->created_at?->toISOString(),
            ]),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Manajemen kelas
     */
    public function classes(Request $request): JsonResponse
    {
        $classes = SchoolClass::with([
            'academicYear:id,year_label',
            'major:id,name',
            'waliKelas:id,name',
        ])
            ->withCount(['students', 'classSubjects'])
            ->when($request->school_id, fn($q) => $q->where('school_id', $request->school_id))
            ->when($request->academic_year_id, fn($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->tingkat, fn($q) => $q->where('tingkat', $request->tingkat))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->search, function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('tingkat', 'like', "%{$request->search}%");
            })
            ->orderBy('tingkat')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes->map(fn($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'tingkat' => $c->tingkat,
                'jenjang' => $c->jenjang,
                'academic_year' => $c->academicYear?->year_label,
                'major' => $c->major?->name,
                'wali_kelas' => $c->waliKelas?->name,
                'student_count' => $c->students_count,
                'subject_count' => $c->class_subjects_count,
                'is_active' => $c->is_active,
            ]),
        ]);
    }

    /**
     * Manajemen mata pelajaran
     */
    public function subjects(Request $request): JsonResponse
    {
        $subjects = Subject::when($request->school_id, fn($q) => $q->where('school_id', $request->school_id))
            ->when($request->group, fn($q) => $q->where('group', $request->group))
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('group')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects->map(fn($s) => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'group' => $s->group,
                'kkm' => $s->kkm,
                'is_active' => $s->is_active,
            ]),
        ]);
    }

    /**
     * Manajemen siswa
     */
    public function students(Request $request): JsonResponse
    {
        $students = Student::with([
            'class:id,code,tingkat',
            'class.waliKelas:id,name',
            'user:id,name,email,phone,photo',
        ])
            ->when($request->school_id, fn($q) => $q->where('school_id', $request->school_id))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('nama_lengkap', 'like', "%{$request->search}%")
                        ->orWhere('nis', 'like', "%{$request->search}%")
                        ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $students->through(fn($s) => [
                'id' => $s->id,
                'nama_lengkap' => $s->nama_lengkap,
                'nis' => $s->nis,
                'nisn' => $s->nisn,
                'jk' => $s->jk,
                'tempat_lahir' => $s->tempat_lahir,
                'tanggal_lahir' => $s->tanggal_lahir?->format('Y-m-d'),
                'agama' => $s->agama,
                'alamat' => $s->alamat,
                'phone' => $s->phone,
                'nama_ayah' => $s->nama_ayah,
                'nama_ibu' => $s->nama_ibu,
                'kelas' => $s->class?->code,
                'tingkat' => $s->class?->tingkat,
                'wali_kelas' => $s->class?->waliKelas?->name,
                'status' => $s->status,
                'tanggal_masuk' => $s->tanggal_masuk?->format('Y-m-d'),
                'email' => $s->user?->email,
                'phone_akun' => $s->user?->phone,
                'photo' => $s->user?->photo,
            ]),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /**
     * Tampilkan detail satu siswa
     */
    public function showStudent(Student $student): JsonResponse
    {
        $student->load([
            'class:id,code,tingkat,wali_kelas_id',
            'class.waliKelas:id,name,phone,photo',
            'user:id,name,email,phone,photo',
        ]);

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
     * Tambah siswa baru (sekaligus buat user akun)
     */
    public function storeStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:200',
            'email'     => ['required','email', Rule::unique('users')],
            'password'  => 'required|string|min:6',
            'nisn'      => ['required','string','max:10', Rule::unique('students','nisn')->where('status','aktif')->whereNull('deleted_at')],
            'nis'       => 'required|string|max:20',
            'jk'        => 'required|in:L,P',
            'class_id'  => 'nullable|exists:classes,id',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'agama'     => 'nullable|string|max:20',
            'alamat'    => 'nullable|string',
            'phone'     => 'nullable|string|max:20',
            'nama_ayah' => 'nullable|string|max:200',
            'nama_ibu'  => 'nullable|string|max:200',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Buat user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'siswa',
            'school_id' => $request->school_id,
            'is_active' => true,
        ]);

        // Upload foto
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/siswa', 'public');
            $user->update(['photo' => $path]);
        }

        // Buat student
        $student = Student::create([
            'user_id' => $user->id,
            'school_id' => $request->school_id,
            'class_id' => $validated['class_id'] ?? null,
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'nama_lengkap' => $validated['name'],
            'jk' => $validated['jk'],
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'agama' => $validated['agama'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'nama_ayah' => $validated['nama_ayah'] ?? null,
            'nama_ibu' => $validated['nama_ibu'] ?? null,
            'status' => 'aktif',
            'tanggal_masuk' => now(),
        ]);

        $student->load(['class.waliKelas:id,name,phone,photo']);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan',
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
                'kelas' => $student->class?->code,
                'tingkat' => $student->class?->tingkat,
                'wali_kelas' => $student->class?->waliKelas ? [
                    'id' => $student->class->waliKelas->id,
                    'name' => $student->class->waliKelas->name,
                    'phone' => $student->class->waliKelas->phone,
                    'photo' => $student->class->waliKelas->photo,
                ] : null,
                'akun' => [
                    'email' => $user->email,
                    'photo' => $user->photo,
                ],
            ],
        ], 201);
    }

    /**
     * Update data siswa
     */
    public function updateStudent(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|string|max:200',
            'nisn' => ['sometimes','string','max:10', Rule::unique('students','nisn')->ignore($student->id)->where('status','aktif')->whereNull('deleted_at')],
            'nis' => 'sometimes|string|max:20',
            'jk' => 'sometimes|in:L,P',
            'class_id' => 'sometimes|nullable|exists:classes,id',
            'tempat_lahir' => 'sometimes|nullable|string|max:100',
            'tanggal_lahir' => 'sometimes|nullable|date',
            'agama' => 'sometimes|nullable|string|max:20',
            'alamat' => 'sometimes|nullable|string',
            'phone' => 'sometimes|nullable|string|max:20',
            'nama_ayah' => 'sometimes|nullable|string|max:200',
            'nama_ibu' => 'sometimes|nullable|string|max:200',
            'status' => 'sometimes|in:aktif,tidak_aktif,lulus,pindah,keluar',
            'email' => ['nullable','email', Rule::unique('users')->ignore($student->user_id)],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $student->update($validated);

        // Update data user
        if ($student->user_id) {
            $userData = [];
            if ($request->has('nama_lengkap')) {
                $userData['name'] = $validated['nama_lengkap'];
            }
            if ($request->has('email')) {
                $userData['email'] = $validated['email'];
            }
            // Upload foto
            if ($request->hasFile('photo')) {
                $user = User::find($student->user_id);
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }
                $userData['photo'] = $request->file('photo')->store('photos/siswa', 'public');
            }
            if (!empty($userData)) {
                User::where('id', $student->user_id)->update($userData);
            }
        }

        $student->load(['class.waliKelas:id,name,phone,photo', 'user:id,name,email,phone,photo']);

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil diperbarui',
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
                'kelas' => $student->class?->code,
                'tingkat' => $student->class?->tingkat,
                'wali_kelas' => $student->class?->waliKelas ? [
                    'id' => $student->class->waliKelas->id,
                    'name' => $student->class->waliKelas->name,
                    'phone' => $student->class->waliKelas->phone,
                    'photo' => $student->class->waliKelas->photo,
                ] : null,
                'akun' => $student->user ? [
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                    'photo' => $student->user->photo,
                ] : null,
            ],
        ]);
    }

    /**
     * Hapus siswa (hard delete, termasuk akun user)
     */
    public function destroyStudent(Student $student): JsonResponse
    {
        // Hapus akun user terkait jika ada — beserta relasinya (cegah FK constraint)
        if ($student->user_id) {
            $user = \App\Models\User::find($student->user_id);
            if ($user) {
                $user->notifications()->delete();
                $user->classSubjectsAsTeacher()->delete();
                $user->createdGrades()->delete();
                $user->createdQuestions()->delete();
                $user->createdExams()->delete();
                $user->verifiedPayments()->delete();
                $user->createdInvoices()->delete();
                $user->createdQuestionBanks()->delete();
                $user->createdP5Projects()->delete();
                $user->createdP5Assessments()->delete();
                $user->createdAttendances()->delete();
                $user->teacherAssignments()->delete();
                $user->delete();
            }
        }

        // Hapus data siswa
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil dihapus',
        ]);
    }
}
