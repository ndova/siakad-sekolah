<?php

namespace App\Services\Dapodik;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\Semester;

/**
 * Validates data against Dapodik & Kurikulum Merdeka SMK rules.
 */
class DapodikValidator
{
    /**
     * Validate rombel beban jam per minggu (Kurmer SMK: 42-46 JP).
     */
    public static function validateRombelBebanJam(SchoolClass $class, Semester $semester): array
    {
        $errors = [];
        $totalJP = $class->classSubjects()
            ->where('semester_id', $semester->id)
            ->sum('jam_per_minggu');

        $pklJP = $class->classSubjects()
            ->where('semester_id', $semester->id)
            ->whereHas('subject', fn($q) => $q->where('is_pkl', true))
            ->sum('jam_per_minggu');

        $batasMin = 42;
        $batasMax = 46;

        if ($totalJP < $batasMin) {
            $errors[] = "Rombel {$class->code}: total JP/minggu ({$totalJP}) kurang dari minimum {$batasMin}";
        }
        if ($totalJP > $batasMax) {
            $errors[] = "Rombel {$class->code}: total JP/minggu ({$totalJP}) melebihi maksimum {$batasMax}";
        }

        // Kelas XI dan XII wajib ada PKL
        if (in_array($class->tingkat, [11, 12]) && $pklJP == 0) {
            $errors[] = "Rombel {$class->code} (tingkat {$class->tingkat}): mapel PKL wajib tersedia";
        }

        return $errors;
    }

    /**
     * Validate guru beban mengajar (max 40 JP/minggu).
     */
    public static function validateGuruBebanMengajar(User $teacher, Semester $semester): array
    {
        $errors = [];
        $totalJP = \App\Models\TeacherAssignment::where('user_id', $teacher->id)
            ->where('semester_id', $semester->id)
            ->sum('jam_per_minggu');

        $maksJP = 40;

        if ($totalJP > $maksJP) {
            $errors[] = "Guru {$teacher->name}: beban {$totalJP} JP melebihi maksimum {$maksJP} JP";
        }

        return $errors;
    }

    /**
     * Validate NISN format and uniqueness.
     */
    public static function validateNISN(string $nisn, ?string $excludeStudentId = null): array
    {
        $errors = [];

        if (strlen($nisn) !== 10 || !ctype_digit($nisn)) {
            $errors[] = "NISN {$nisn}: harus 10 digit angka";
        }

        $query = Student::where('nisn', $nisn)->where('status', 'aktif');
        if ($excludeStudentId) {
            $query->where('id', '!=', $excludeStudentId);
        }

        if ($query->exists()) {
            $errors[] = "NISN {$nisn}: sudah digunakan oleh siswa aktif lain";
        }

        return $errors;
    }

    /**
     * Validate rombel structure completeness.
     */
    public static function validateRombelStructure(SchoolClass $class): array
    {
        $errors = [];

        if (!$class->wali_kelas_id) {
            $errors[] = "Rombel {$class->code}: belum memiliki wali kelas";
        }

        $studentCount = $class->students()->where('status', 'aktif')->count();
        $kapasitas = $class->kapasitas ?? 36;

        if ($studentCount > $kapasitas) {
            $errors[] = "Rombel {$class->code}: jumlah siswa ({$studentCount}) melebihi kapasitas ({$kapasitas})";
        }

        if ($studentCount === 0) {
            $errors[] = "Rombel {$class->code}: tidak memiliki siswa aktif";
        }

        return $errors;
    }

    /**
     * Validate total PKL hours per student (Kurmer: min 790 JP total).
     */
    public static function validatePklTotalJam(Student $student): array
    {
        $errors = [];
        $totalJam = $student->pklRecords()->sum('total_jam');
        $minJam = 790;

        if ($totalJam > 0 && $totalJam < $minJam) {
            $errors[] = "Siswa {$student->nama_lengkap}: total jam PKL ({$totalJam}) kurang dari minimum {$minJam} JP";
        }

        return $errors;
    }

    /**
     * Run all validations and return grouped errors.
     */
    public static function validateAll(SchoolClass $class, Semester $semester): array
    {
        $allErrors = [];

        $allErrors['beban_jam'] = self::validateRombelBebanJam($class, $semester);
        $allErrors['struktur_rombel'] = self::validateRombelStructure($class);

        foreach ($class->students()->where('status', 'aktif')->get() as $student) {
            $nisnErrors = self::validateNISN($student->nisn, $student->id);
            if (!empty($nisnErrors)) {
                $allErrors["nisn_{$student->nisn}"] = $nisnErrors;
            }
        }

        return array_filter($allErrors, fn($e) => !empty($e));
    }
}
