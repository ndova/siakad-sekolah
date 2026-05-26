<?php

namespace App\Traits;

trait HasSchoolScope
{
    protected function schoolId(): string
    {
        return auth()->user()->school_id ?? '';
    }
}
