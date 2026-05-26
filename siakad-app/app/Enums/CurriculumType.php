<?php

namespace App\Enums;

/**
 * Jenis kurikulum yang aktif di sekolah.
 * Digunakan untuk menentukan fitur/modul yang tampil.
 */
enum CurriculumType: string
{
    case KURMER = 'kurmer';     // Kurikulum Merdeka
    case K13    = 'k13';        // Kurikulum 2013 (non-Merdeka)

    public function label(): string
    {
        return match ($this) {
            self::KURMER => 'Kurikulum Merdeka',
            self::K13    => 'Kurikulum 2013 (K13)',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::KURMER => 'sparkles',
            self::K13    => 'book-marked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::KURMER => '#059669',
            self::K13    => '#2563eb',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::KURMER => 'Mendukung P5, PKL, TP/ATP, fase E/F, Projek Profil Pelajar Pancasila, dan penilaian berbasis tujuan pembelajaran.',
            self::K13    => 'Mendukung KI-3 (pengetahuan), KI-4 (keterampilan), KKM per mapel, dan rapor format K13 standar.',
        };
    }

    /**
     * Apakah kurikulum ini mendukung modul P5?
     */
    public function supportsP5(): bool
    {
        return $this === self::KURMER;
    }

    /**
     * Apakah kurikulum ini mendukung modul PKL terpisah?
     */
    public function supportsPkl(): bool
    {
        return $this === self::KURMER;
    }

    /**
     * Apakah kurikulum ini menggunakan TP (Tujuan Pembelajaran)?
     */
    public function usesTP(): bool
    {
        return $this === self::KURMER;
    }

    /**
     * Label fase/kelas.
     */
    public function classLabel(int $tingkat): string
    {
        if ($this === self::KURMER) {
            return match ($tingkat) {
                10 => 'X (Fase E)',
                11 => 'XI (Fase F)',
                12 => 'XII (Fase F)',
                default => (string) $tingkat,
            };
        }
        return match ($tingkat) {
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => (string) $tingkat,
        };
    }

    /**
     * Get from session, default to KURMER.
     */
    public static function fromSession(): self
    {
        $val = session('curriculum_type', self::KURMER->value);
        return self::tryFrom($val) ?? self::KURMER;
    }

    /**
     * Get all curricula enabled by admin for this school.
     * Returns array of CurriculumType cases.
     */
    public static function available(): array
    {
        $school = \App\Services\SchoolService::get();
        if (!$school) return [self::KURMER];

        $available = [];
        if ($school->kurikulum_kurmer_enabled ?? true) {
            $available[] = self::KURMER;
        }
        if ($school->kurikulum_k13_enabled ?? false) {
            $available[] = self::K13;
        }

        // Fallback: minimal satu kurikulum harus tersedia
        if (empty($available)) {
            $available[] = self::KURMER;
        }

        return $available;
    }

    /**
     * Check if current session curriculum is still valid (enabled).
     */
    public static function isSessionValid(): bool
    {
        $current = self::fromSession();
        return in_array($current, self::available(), true);
    }

    /**
     * Get the single default curriculum if only one is enabled.
     * Returns null if multiple are available (user must choose).
     */
    public static function autoSelectIfSingle(): ?self
    {
        $available = self::available();
        return count($available) === 1 ? $available[0] : null;
    }
}
