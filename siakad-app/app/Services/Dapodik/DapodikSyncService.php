<?php

namespace App\Services\Dapodik;

use App\Models\SyncLog;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\PklRecord;
use Illuminate\Support\Collection;

/**
 * Orchestrates sync operations: export & import workflows.
 */
class DapodikSyncService
{
    public function __construct(
        protected DapodikMapper $mapper = new DapodikMapper(),
        protected DapodikValidator $validator = new DapodikValidator(),
        protected DapodikExporter $exporter = new DapodikExporter(),
    ) {}

    // ─── EXPORT ──────────────────────────────────────────────

    /**
     * Export students & rombel to CSV.
     */
    public function exportStudents(Collection $classIds, string $semesterId, string $triggeredBy): array
    {
        $log = SyncLog::create([
            'direction' => SyncLog::DIRECTION_EXPORT,
            'entity_type' => 'student',
            'status' => SyncLog::STATUS_PROCESSING,
            'triggered_by' => $triggeredBy,
            'started_at' => now(),
        ]);

        try {
            $semester = Semester::findOrFail($semesterId);
            $classes = SchoolClass::whereIn('id', $classIds->toArray())->get();
            $students = Student::with(['class.major', 'class.specialization'])
                ->whereIn('class_id', $classIds->toArray())
                ->where('status', 'aktif')
                ->get();

            $log->total_records = $students->count();

            // Validasi
            $allErrors = [];
            foreach ($classes as $class) {
                $errs = $this->validator->validateRombelBebanJam($class, $semester);
                if (!empty($errs)) $allErrors[$class->code] = $errs;
            }

            if (!empty($allErrors)) {
                $log->markFailed(json_encode($allErrors));
                return ['success' => false, 'errors' => $allErrors, 'log_id' => $log->id];
            }

            // Map & export
            $rows = $students->map(fn($s) => $this->mapper->mapStudent($s))->toArray();
            $filePath = $this->exporter->toCsv('siswa_rombel', $rows);
            $log->markSuccess($students->count(), $filePath);

            return [
                'success' => true,
                'total' => $students->count(),
                'download_url' => $this->exporter->downloadUrl($filePath),
                'log_id' => $log->id,
            ];
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'log_id' => $log->id];
        }
    }

    /**
     * Export teacher assignments & schedules to CSV.
     */
    public function exportTeachersAssignments(string $semesterId, string $triggeredBy): array
    {
        $log = SyncLog::create([
            'direction' => SyncLog::DIRECTION_EXPORT,
            'entity_type' => 'teacher_assignment',
            'status' => SyncLog::STATUS_PROCESSING,
            'triggered_by' => $triggeredBy,
            'started_at' => now(),
        ]);

        try {
            $assignments = \App\Models\TeacherAssignment::with(['user', 'classSubject.subject', 'classSubject.schoolClass'])
                ->where('semester_id', $semesterId)
                ->get();

            $log->total_records = $assignments->count();

            $rows = $assignments->map(function ($a) {
                $teacher = $this->mapper->mapTeacher($a->user);
                $teacher['kode_rombel'] = DapodikMapping::getCode('class', $a->classSubject->class_id);
                $teacher['kode_mapel'] = DapodikMapping::getCode('subject', $a->classSubject->subject_id);
                $teacher['nama_mapel'] = $a->classSubject->subject->name ?? '';
                $teacher['nama_rombel'] = $a->classSubject->schoolClass->code ?? '';
                $teacher['jam_per_minggu'] = $a->jam_per_minggu;
                return $teacher;
            })->toArray();

            $filePath = $this->exporter->toCsv('guru_jadwal', $rows);
            $log->markSuccess($assignments->count(), $filePath);

            return [
                'success' => true,
                'total' => $assignments->count(),
                'download_url' => $this->exporter->downloadUrl($filePath),
                'log_id' => $log->id,
            ];
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'log_id' => $log->id];
        }
    }

    /**
     * Export grades & reports.
     */
    public function exportGrades(Collection $classIds, string $semesterId, string $triggeredBy): array
    {
        $log = SyncLog::create([
            'direction' => SyncLog::DIRECTION_EXPORT,
            'entity_type' => 'grade',
            'status' => SyncLog::STATUS_PROCESSING,
            'triggered_by' => $triggeredBy,
            'started_at' => now(),
        ]);

        try {
            $reports = \App\Models\Report::with(['student', 'student.class'])
                ->where('semester_id', $semesterId)
                ->whereHas('student', fn($q) => $q->whereIn('class_id', $classIds->toArray()))
                ->get();

            $rows = [];
            foreach ($reports as $r) {
                $base = [
                    'nisn' => $r->student->nisn,
                    'nama' => $r->student->nama_lengkap,
                    'kode_rombel' => DapodikMapping::getCode('class', $r->student->class_id),
                ];

                // Mapel
                foreach ($r->mapelList ?? [] as $m) {
                    $rows[] = array_merge($base, [
                        'jenis' => 'intrakurikuler',
                        'kode_mapel' => DapodikMapping::getCode('subject', $m['subject_id'] ?? ''),
                        'nilai_akhir' => $m['nilai_akhir'] ?? '',
                        'predikat' => $m['predikat'] ?? '',
                    ]);
                }

                // P5
                foreach ($r->p5Projects ?? [] as $p5) {
                    $rows[] = array_merge($base, [
                        'jenis' => 'P5',
                        'kode_projek' => DapodikMapping::getCode('p5_project', $p5['project_id'] ?? ''),
                        'tema' => $p5['tema'] ?? '',
                        'dimensi' => json_encode($p5['dimensi'] ?? []),
                    ]);
                }

                // PKL
                if ($r->pklRecord ?? null) {
                    $rows[] = array_merge($base, [
                        'jenis' => 'PKL',
                        'dudi' => $r->pklRecord->nama_dudi ?? '',
                        'total_jam' => $r->pklRecord->total_jam ?? 0,
                        'rata_nilai' => $r->pklRecord->rataRata() ?? '',
                        'predikat' => $r->pklRecord->predikat() ?? '',
                    ]);
                }
            }

            $log->total_records = count($rows);
            $filePath = $this->exporter->toCsv('nilai_rapor', $rows);
            $log->markSuccess(count($rows), $filePath);

            return [
                'success' => true,
                'total' => count($rows),
                'download_url' => $this->exporter->downloadUrl($filePath),
                'log_id' => $log->id,
            ];
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'log_id' => $log->id];
        }
    }

    // ─── IMPORT ──────────────────────────────────────────────

    /**
     * Import master data from Dapodik CSV.
     */
    public function importMasterData(string $filePath, string $triggeredBy): array
    {
        // Placeholder — implementasi detail disesuaikan dengan format CSV Dapodik
        return [
            'success' => false,
            'message' => 'Fitur import dari file Dapodik akan diimplementasikan pada fase berikutnya.',
        ];
    }
}
