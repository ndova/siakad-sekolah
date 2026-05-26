<?php

namespace App\Services\Dapodik;

use App\Models\DapodikMapping;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\PklRecord;
use App\Models\P5Project;
use Illuminate\Support\Collection;

/**
 * Central mapper: SIAKAD internal ↔ Dapodik field/format.
 * All Dapodik-specific logic stays here.
 */
class DapodikMapper
{
    // ─── STUDENT ─────────────────────────────────────────────

    public static function mapStudent(Student $student): array
    {
        return [
            'nisn'           => $student->nisn,
            'nis'            => $student->nis,
            'nik'            => $student->nik,
            'nama'           => $student->nama_lengkap,
            'tempat_lahir'   => $student->tempat_lahir,
            'tanggal_lahir'  => $student->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin'  => $student->jk,
            'agama'          => $student->agama,
            'alamat'         => $student->alamat,
            'kode_rombel'    => DapodikMapping::getCode('class', $student->class_id),
            'status'         => self::mapStudentStatus($student->status),
            'tanggal_masuk'  => $student->tanggal_masuk?->format('Y-m-d'),
            'nama_ayah'      => $student->nama_ayah,
            'nama_ibu'       => $student->nama_ibu,
        ];
    }

    public static function mapStudentStatus(string $status): string
    {
        return match ($status) {
            'aktif'   => '1',   // Aktif
            'lulus'   => '2',   // Lulus
            'mutasi'  => '3',   // Mutasi
            'do'      => '4',   // Drop Out
            default   => '1',
        };
    }

    // ─── CLASS / ROMBEL ──────────────────────────────────────

    public static function mapRombel(SchoolClass $class): array
    {
        return [
            'kode_rombel'        => DapodikMapping::getCode('class', $class->id) ?? $class->code,
            'nama_rombel'        => $class->code,
            'tingkat'            => $class->tingkat,
            'kode_program'       => DapodikMapping::getCode('major', $class->major_id),
            'kode_konsentrasi'   => DapodikMapping::getCode('specialization', $class->specialization_id),
            'wali_kelas'         => $class->waliKelas?->name,
            'nuptk_wali'         => $class->waliKelas?->nuptk,
            'jumlah_siswa'       => $class->students()->where('status', 'aktif')->count(),
            'kapasitas'          => $class->kapasitas ?? 36,
        ];
    }

    // ─── SUBJECT ─────────────────────────────────────────────

    public static function mapSubject(Subject $subject): array
    {
        return [
            'kode_mapel'   => DapodikMapping::getCode('subject', $subject->id) ?? $subject->code,
            'nama_mapel'   => $subject->name,
            'kelompok'     => $subject->kelompok ?? 'A',
            'is_pkl'       => $subject->is_pkl ?? false,
            'is_p5'        => $subject->is_p5 ?? false,
            'fase'         => $subject->fase,
            'jam_semester' => $subject->jam_semester,
        ];
    }

    // ─── TEACHER / GTK ───────────────────────────────────────

    public static function mapTeacher(User $teacher): array
    {
        return [
            'nuptk'          => $teacher->nuptk,
            'nip'            => $teacher->nip,
            'nik'            => $teacher->nik,
            'nama'           => $teacher->name,
            'jenis_gtk'      => $teacher->jenis_gtk,
            'status_pegawai' => $teacher->status_kepegawaian,
            'role_siakad'    => $teacher->role,
        ];
    }

    // ─── PKL ─────────────────────────────────────────────────

    public static function mapPklRecord(PklRecord $pkl): array
    {
        return [
            'nisn'             => $pkl->student->nisn,
            'nama_siswa'       => $pkl->student->nama_lengkap,
            'kode_rombel'      => DapodikMapping::getCode('class', $pkl->class_id),
            'nama_dudi'        => $pkl->nama_dudi,
            'alamat_dudi'      => $pkl->alamat_dudi,
            'pembimbing_dudi'  => $pkl->pembimbing_dudi,
            'tanggal_mulai'    => $pkl->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai'  => $pkl->tanggal_selesai?->format('Y-m-d'),
            'total_jam'        => $pkl->total_jam,
            'rata_nilai'       => $pkl->rataRata(),
            'predikat'         => $pkl->predikat(),
        ];
    }

    // ─── P5 ──────────────────────────────────────────────────

    public static function mapP5Project(P5Project $project, array $studentAssessments): array
    {
        return [
            'kode_projek' => DapodikMapping::getCode('p5_project', $project->id) ?? $project->judul,
            'tema'        => $project->tema,
            'judul'       => $project->judul,
            'total_jam'   => $project->total_jam,
            'siswa'       => $studentAssessments,
        ];
    }

    // ─── BULK ────────────────────────────────────────────────

    /**
     * Generate an array of rows for CSV export.
     */
    public static function toCsvRows(Collection $items, string $entityType): array
    {
        $rows = [];
        foreach ($items as $item) {
            $rows[] = match ($entityType) {
                'student'    => self::mapStudent($item),
                'class'      => self::mapRombel($item),
                'subject'    => self::mapSubject($item),
                'teacher'    => self::mapTeacher($item),
                'pkl'        => self::mapPklRecord($item),
                default      => [],
            };
        }
        return $rows;
    }
}
