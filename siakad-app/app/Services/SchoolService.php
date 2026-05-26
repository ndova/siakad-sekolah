<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\Cache;

class SchoolService
{
    /**
     * Get current active school settings.
     */
    public static function get(): ?School
    {
        $cached = Cache::get('school_settings');

        // If cached value is invalid (e.g., __PHP_Incomplete_Class), clear and re-fetch
        if ($cached && $cached instanceof School) {
            return $cached;
        }

        // Clear any stale cache
        if ($cached !== null) {
            Cache::forget('school_settings');
        }

        $school = School::where('is_active', true)->first();

        if ($school) {
            Cache::put('school_settings', $school, 3600);
        }

        return $school;
    }

    /**
     * Get a specific setting value with fallback.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $school = self::get();
        if (!$school) return $default;
        return $school->{$key} ?? $default;
    }

    /**
     * Clear the cache after settings update.
     */
    public static function clearCache(): void
    {
        Cache::forget('school_settings');
    }
}
