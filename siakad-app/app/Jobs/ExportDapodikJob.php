<?php

namespace App\Jobs;

use App\Services\Dapodik\DapodikSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ExportDapodikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $entityType,
        public Collection $classIds,
        public string $semesterId,
        public string $triggeredBy,
    ) {}

    public function handle(DapodikSyncService $sync): void
    {
        match ($this->entityType) {
            'student'  => $sync->exportStudents($this->classIds, $this->semesterId, $this->triggeredBy),
            'teacher'  => $sync->exportTeachersAssignments($this->semesterId, $this->triggeredBy),
            'grade'    => $sync->exportGrades($this->classIds, $this->semesterId, $this->triggeredBy),
            default    => null,
        };
    }
}
