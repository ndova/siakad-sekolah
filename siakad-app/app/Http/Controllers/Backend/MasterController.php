<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    protected function schoolId() { return auth()->user()->school_id; }

    private function perPage(Request $request): int
    {
        return in_array((int) $request->get('per_page'), [10, 20, 50, 100])
            ? (int) $request->get('per_page')
            : 20;
    }

    // ─── USERS ────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $perPage = $this->perPage($request);
        $users = User::where('school_id', $this->schoolId())
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status === 'aktif'))
            ->when($request->search, fn($q, $s) => $q->where(fn($q) => 
                $q->where('name','like',"%$s%")->orWhere('email','like',"%$s%")
            ))
            ->orderBy('name')->paginate($perPage)->withQueryString();
        $subjects = Subject::where('school_id', $this->schoolId())->where('is_active', true)->orderBy('code')->get();
        return view('backend.master.users', compact('users', 'subjects', 'perPage'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:200',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:' . implode(',', \App\Enums\Role::allValues()),
            'nip'      => 'nullable|string|max:30',
            'phone'    => 'nullable|string|max:20',
            // Data siswa (jika role=siswa)
            'nis'      => 'required_if:role,siswa|string|max:20',
            'nisn'     => 'required_if:role,siswa|string|max:10|unique:students,nisn',
            'jk'       => 'required_if:role,siswa|in:L,P',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'agama'     => 'nullable|string|max:20',
            'alamat'    => 'nullable|string',
            'class_id'  => 'nullable|exists:classes,id',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;
        $user = User::create($data);

        // Upload foto
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/siswa', 'public');
            $user->update(['photo' => $path]);
        }

        // Jika role siswa, buat data Student
        if ($data['role'] === 'siswa') {
            Student::create([
                'user_id' => $user->id,
                'school_id' => $this->schoolId(),
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
        }

        // Assign mata pelajaran untuk role guru
        if ($data['role'] === 'guru' && $request->filled('subject_ids')) {
            $subjectIds = (array) $request->subject_ids;
            // Assign ke kelas aktif (placeholder, admin bisa atur ulang via Kelas-Mapel)
            $activeClasses = SchoolClass::where('school_id', $this->schoolId())
                ->where('is_active', true)->pluck('id');
            foreach ($subjectIds as $subjectId) {
                foreach ($activeClasses as $classId) {
                    ClassSubject::firstOrCreate([
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'teacher_id' => $user->id,
                    ]);
                }
            }
        }

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:200',
            'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
            'role'  => 'required|in:' . implode(',', \App\Enums\Role::allValues()),
            'nip'   => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        $user->update($data);

        // Update mata pelajaran untuk role guru
        if ($data['role'] === 'guru' && $request->has('subject_ids')) {
            $subjectIds = (array) $request->subject_ids;
            // Hapus mapping lama, assign ulang
            ClassSubject::where('teacher_id', $user->id)->delete();
            $activeClasses = SchoolClass::where('school_id', $this->schoolId())
                ->where('is_active', true)->pluck('id');
            foreach ($subjectIds as $subjectId) {
                foreach ($activeClasses as $classId) {
                    ClassSubject::firstOrCreate([
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'teacher_id' => $user->id,
                    ]);
                }
            }
        }

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function deleteUser(User $user)
    {
        if ($user->school_id !== $this->schoolId()) abort(403);
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        if ($user->school_id !== $this->schoolId()) abort(403);
        $user->update(['is_active' => (bool) $request->is_active]);
        return back()->with('success', 'Status user diperbarui.');
    }

    // ─── CLASSES ──────────────────────────────────────────────────────
    public function classes(Request $request)
    {
        $user = auth()->user();
        $query = SchoolClass::with(['waliKelas', 'major', 'academicYear'])            ->withCount('students')            ->where('school_id', $this->schoolId());

        // Guru hanya lihat kelas yang dia ampu
        if ($user->role === 'guru') {
            $taughtClassIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $query->whereIn('id', $taughtClassIds);
        }

        $classes = $query->when($request->search, fn($q,$s) => $q->where('code','like',"%$s%")->orWhere('tingkat',$s))
            ->orderBy('tingkat')->orderBy('code')->paginate(20)->withQueryString();
        $academicYears = AcademicYear::where('school_id', $this->schoolId())->orderBy('code','desc')->get();
        $majors = Major::where('school_id', $this->schoolId())->where('is_active', true)->get();
        $teachers = User::where('school_id', $this->schoolId())->whereIn('role',['guru','walikelas'])->orderBy('name')->get();
        return view('backend.master.classes', compact('classes','academicYears','majors','teachers'));
    }

    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'code'   => 'required|string|max:20',
            'tingkat'=> 'required|integer|min:1|max:12',
            'jenjang'=> 'required|in:SMP,SMA,SMK',
            'major_id' => 'nullable|exists:majors,id',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'kapasitas' => 'nullable|integer|min:1',
        ]);
        $data['is_active'] = true;
        SchoolClass::create($data);
        return back()->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'code'   => 'required|string|max:20',
            'tingkat'=> 'nullable|integer|min:1|max:12',
            'jenjang'=> 'nullable|in:SMP,SMA,SMK',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'major_id' => 'nullable|exists:majors,id',
            'kapasitas' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        // Hanya update field yang tidak null/empty (jaga NOT NULL constraint)
        $class->update(array_filter($data, fn($v) => $v !== null && $v !== ''));
        return back()->with('success', 'Rombel diperbarui.');
    }

    public function deleteClass(SchoolClass $class)
    {
        $class->update(['is_active' => false]);
        return back()->with('success', 'Rombel dinonaktifkan.');
    }

    // ─── SUBJECTS ─────────────────────────────────────────────────────
    public function subjects(Request $request)
    {
        $perPage = $this->perPage($request);
        $user = auth()->user();
        $query = Subject::where('school_id', $this->schoolId());

        // Guru hanya lihat mapel yang diampu
        if ($user->role === 'guru') {
            $taughtSubjectIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $query->whereIn('id', $taughtSubjectIds);
        }

        $subjects = $query->when($request->kategori, fn($q,$k)=>$q->where('kategori',$k))
            ->when($request->search, fn($q,$s)=>$q->where('name','like',"%$s%")->orWhere('code','like',"%$s%"))
            ->orderBy('kategori')->orderBy('code')->paginate($perPage)->withQueryString();
        return view('backend.master.subjects', compact('subjects', 'perPage'));
    }

    public function storeSubject(Request $request)
    {
        $data = $request->validate([
            'code'    => 'required|string|max:20',
            'name'    => 'required|string|max:150',
            'kategori'=> 'required|in:umum,kejuruan,muatan_lokal',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;
        Subject::create($data);
        return back()->with('success', 'Mapel berhasil ditambahkan.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'code'    => 'required|string|max:20',
            'name'    => 'required|string|max:150',
            'kategori'=> 'required|in:umum,kejuruan,muatan_lokal',
            'is_active' => 'boolean',
        ]);
        $subject->update($data);
        return back()->with('success', 'Mapel diperbarui.');
    }

    public function deleteSubject(Subject $subject)
    {
        $subject->update(['is_active' => false]);
        return back()->with('success', 'Mapel dinonaktifkan.');
    }

    // ─── STUDENTS ──────────────────────────────────────────────────────
    public function students(Request $request)
    {
        $perPage = $this->perPage($request);
        $user = auth()->user();
        $query = Student::with(['class', 'user', 'parents'])
            ->where('school_id', $this->schoolId());

        // Guru hanya lihat siswa di kelas yang dia ampu
        if ($user->role === 'guru') {
            $taughtClassIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $query->whereIn('class_id', $taughtClassIds);
        }

        $students = $query->when($request->class_id, fn($q,$c)=>$q->where('class_id',$c))
            ->when($request->status, fn($q,$s)=>$q->where('status',$s))
            ->when($request->search, fn($q,$s)=>$q->where(fn($q)=>
                $q->where('nama_lengkap','like',"%$s%")->orWhere('nis','like',"%$s%")->orWhere('nisn','like',"%$s%")
            ))
            ->orderBy('nama_lengkap')->paginate($perPage)->withQueryString();

        // Guru hanya lihat kelas yang dia ampu di dropdown filter
        if ($user->role === 'guru') {
            $taughtClassIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $classes = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true)->whereIn('id', $taughtClassIds)->orderBy('code')->get();
        } else {
            $classes = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true)->orderBy('code')->get();
        }

        return view('backend.master.students', compact('students','classes', 'perPage'));
    }

    public function storeStudent(Request $request)
    {
        $data = $request->validate([
            'nisn'      => 'required|string|max:10|unique:students,nisn',
            'nis'       => 'required|string|max:20',
            'nama_lengkap' => 'required|string|max:200',
            'jk'        => 'required|in:L,P',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'agama'     => 'nullable|string|max:20',
            'alamat'    => 'nullable|string',
            'phone'     => 'nullable|string|max:20',
            'nama_ayah' => 'nullable|string|max:200',
            'nama_ibu'  => 'nullable|string|max:200',
            'class_id'  => 'nullable|exists:classes,id',
            'tanggal_masuk' => 'nullable|date',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['status'] = 'aktif';

        // Upload foto → users table via user_id
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos/siswa', 'public');
        }

        $student = Student::create($data);

        // Simpan foto ke user jika ada
        if ($photoPath && $student->user_id) {
            User::where('id', $student->user_id)->update(['photo' => $photoPath]);
        }

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function updateStudent(Request $request, Student $student)
    {
        $data = $request->validate([
            'nisn'      => ['required','string','max:10', Rule::unique('students')->ignore($student->id)],
            'nis'       => 'required|string|max:20',
            'nama_lengkap' => 'required|string|max:200',
            'jk'        => 'required|in:L,P',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'agama'     => 'nullable|string|max:20',
            'alamat'    => 'nullable|string',
            'phone'     => 'nullable|string|max:20',
            'nama_ayah' => 'nullable|string|max:200',
            'nama_ibu'  => 'nullable|string|max:200',
            'class_id'  => 'nullable|exists:classes,id',
            'status'    => 'required|in:aktif,lulus,pindah,keluar',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $student->update($data);

        // Upload foto ke user
        if ($request->hasFile('photo') && $student->user_id) {
            $user = User::find($student->user_id);
            if ($user->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
            }
            $path = $request->file('photo')->store('photos/siswa', 'public');
            $user->update(['photo' => $path]);
        }

        return back()->with('success', 'Data siswa diperbarui.');
    }

    public function deleteStudent(Student $student)
    {
        $student->update(['status' => 'keluar']);
        return back()->with('success', 'Siswa dikeluarkan.');
    }

    public function showStudent(Student $student)
    {
        // Pastikan siswa dari sekolah yang sama
        if ($student->school_id !== $this->schoolId()) {
            abort(404);
        }

        $student->load([
            'class:id,code,tingkat,wali_kelas_id',
            'class.waliKelas:id,name,phone',
            'user:id,name,email,phone,photo',
            'parents:id,nama_lengkap,hubungan,phone',
        ]);

        // Ambil wali utama & daftar wali
        $primaryParent = $student->parents->firstWhere('pivot.is_primary', true);
        $waliList = $student->parents->map(fn($p) => [
            'nama'     => $p->nama_lengkap,
            'hubungan' => $p->hubungan,
            'phone'    => $p->phone,
            'is_primary' => (bool) ($p->pivot->is_primary ?? false),
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id'             => $student->id,
                'nama_lengkap'   => $student->nama_lengkap,
                'nis'            => $student->nis,
                'nisn'           => $student->nisn,
                'nik'            => $student->nik,
                'kode_dapodik'   => $student->kode_dapodik,
                'jk'             => $student->jk,
                'tempat_lahir'   => $student->tempat_lahir,
                'tanggal_lahir'  => $student->tanggal_lahir?->format('d M Y'),
                'agama'          => $student->agama,
                'alamat'         => $student->alamat,
                'phone'          => $student->phone,
                'nama_ayah'      => $student->nama_ayah,
                'nama_ibu'       => $student->nama_ibu,
                'nama_wali'      => $primaryParent?->nama_lengkap ?? $student->nama_ayah ?? $student->nama_ibu,
                'wali_list'      => $waliList,
                'status'         => $student->status,
                'tanggal_masuk'  => $student->tanggal_masuk?->format('d M Y'),
                'kelas'          => $student->class?->code,
                'tingkat'        => $student->class?->tingkat,
                'wali_kelas'     => $student->class?->waliKelas?->name,
                'email'          => $student->user?->email,
                'phone_akun'     => $student->user?->phone,
                'photo'          => $student->user?->photo ? asset('storage/'.$student->user->photo) : null,
            ],
        ]);
    }

    // ─── TEACHERS (GTK) ────────────────────────────────────────────────
    public function teachers(Request $request)
    {
        $perPage = $this->perPage($request);
        $teachers = User::with('homeroomClass')
            ->where('school_id', $this->schoolId())
            ->whereIn('role', ['guru','walikelas','kepsek','admin','tata_usaha','bk','perpustakaan','bendahara'])
            ->when($request->role, fn($q,$r)=>$q->where('role',$r))
            ->when($request->search, fn($q,$s)=>$q->where('name','like',"%$s%"))
            ->orderBy('name')->paginate($perPage)->withQueryString();
        $classes = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true)->get();
        return view('backend.master.teachers', compact('teachers','classes', 'perPage'));
    }

    // ─── ACADEMIC YEARS + SEMESTERS ────────────────────────────────────
    public function academicSetup(Request $request)
    {
        $years = AcademicYear::where('school_id', $this->schoolId())->with('semesters')->orderBy('code','desc')->get();
        return view('backend.master.academic-setup', compact('years'));
    }

    public function storeAcademicYear(Request $request)
    {
        $data = $request->validate([
            'code'       => 'required|string|max:9',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        $data['school_id'] = $this->schoolId();
        AcademicYear::create($data);
        return back()->with('success','Tahun ajaran ditambahkan.');
    }

    public function toggleAcademicYear(AcademicYear $year)
    {
        AcademicYear::where('school_id', $this->schoolId())->update(['is_active' => false]);
        $year->update(['is_active' => true]);
        return back()->with('success','Tahun ajaran diaktifkan.');
    }

    public function updateAcademicYear(Request $request, AcademicYear $year)
    {
        $data = $request->validate([
            'code'       => 'required|string|max:9',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        $year->update($data);
        return back()->with('success','Tahun ajaran diperbarui.');
    }

    public function deleteAcademicYear(AcademicYear $year)
    {
        $year->delete();
        return back()->with('success','Tahun ajaran berhasil dihapus.');
    }

    public function storeSemester(Request $request, AcademicYear $year)
    {
        $data = $request->validate([
            'semester_number' => 'required|integer|min:1|max:2',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        $data['academic_year_id'] = $year->id;
        Semester::create($data);
        return back()->with('success','Semester ditambahkan.');
    }

    public function toggleSemester(Semester $semester)
    {
        Semester::where('academic_year_id', $semester->academic_year_id)->update(['is_active' => false]);
        $semester->update(['is_active' => true]);
        return back()->with('success','Semester diaktifkan.');
    }

    public function deleteSemester(Semester $semester)
    {
        $semester->delete();
        return back()->with('success','Semester berhasil dihapus.');
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'semester_number' => 'sometimes|integer|min:1|max:2',
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date|after:start_date',
        ]);
        $semester->update($data);
        return back()->with('success','Semester diperbarui.');
    }

    // ─── CLASS-SUBJECT MAPPING ─────────────────────────────────────────
    public function classSubjectMapping(Request $request)
    {
        $user = auth()->user();
        $query = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true);
        $subjectQuery = Subject::where('school_id', $this->schoolId())->where('is_active',true);
        $teacherQuery = User::where('school_id', $this->schoolId())->whereIn('role',['guru','walikelas']);

        // Guru hanya lihat data dirinya sendiri
        if ($user->role === 'guru') {
            $taughtClassIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('class_id')->unique();
            $taughtSubjectIds = ClassSubject::where('teacher_id', $user->id)
                ->whereHas('schoolClass', fn($q) => $q->where('school_id', $this->schoolId()))
                ->pluck('subject_id')->unique();
            $query->whereIn('id', $taughtClassIds);
            $subjectQuery->whereIn('id', $taughtSubjectIds);
            $teacherQuery->where('id', $user->id);
        }

        $classes  = $query->orderBy('code')->get();
        $subjects = $subjectQuery->orderBy('code')->get();
        $teachers = $teacherQuery->orderBy('name')->get();
        $classId = $request->class_id ?? optional($classes->first())->id;

        // Filter semester berdasarkan tahun ajaran kelas yg dipilih
        $semestersQuery = Semester::whereHas('academicYear', fn($q) => $q->where('school_id', $this->schoolId()));
        if ($classId) {
            $selectedClass = $classes->firstWhere('id', $classId);
            if ($selectedClass?->academic_year_id) {
                $semestersQuery->where('academic_year_id', $selectedClass->academic_year_id);
            }
        }
        $semesters = $semestersQuery->orderBy('semester_number')->get();

        $mappingQuery = \App\Models\ClassSubject::with(['subject','teacher','semester'])
            ->whereHas('schoolClass', fn($q)=>$q->where('school_id',$this->schoolId()));
        if ($user->role === 'guru') {
            $mappingQuery->where('teacher_id', $user->id);
        }
        $mappings = $mappingQuery->when($classId, fn($q)=>$q->where('class_id',$classId))
            ->orderBy('class_id')->paginate(20)->withQueryString();

        return view('backend.master.class-subject', compact('classes','subjects','teachers','semesters','mappings','classId'));
    }

    public function storeClassSubject(Request $request)
    {
        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'semester_id'=> 'nullable|exists:semesters,id',
            'teacher_id' => 'required|exists:users,id',
            'kkm'        => 'nullable|numeric|min:0|max:100',
            'jam_per_minggu' => 'nullable|integer|min:1|max:40',
        ]);
        \App\Models\ClassSubject::updateOrCreate(
            ['class_id'=>$data['class_id'], 'subject_id'=>$data['subject_id']],
            $data
        );
        return back()->with('success','Mapping mapel disimpan.');
    }

    public function deleteClassSubject(\App\Models\ClassSubject $mapping)
    {
        $mapping->delete();
        return back()->with('success','Mapping dihapus.');
    }

    public function updateClassSubject(Request $request, \App\Models\ClassSubject $mapping)
    {
        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'semester_id'=> 'nullable|exists:semesters,id',
            'teacher_id' => 'required|exists:users,id',
            'kkm'        => 'nullable|numeric|min:0|max:100',
            'jam_per_minggu' => 'nullable|integer|min:1|max:40',
        ]);
        $mapping->update($data);
        return back()->with('success','Mapping mapel diperbarui.');
    }
}
