<?php

namespace App\Enums;

enum Role: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case GURU = 'guru';
    case WALIKELAS = 'walikelas';
    case BENDAHARA = 'bendahara';
    case KEPSEK = 'kepsek';
    case SISWA = 'siswa';
    case ORANGTUA = 'orang_tua';
    case TATAUSAHA = 'tata_usaha';
    case BK = 'bk';
    case PERPUS = 'perpustakaan';

    public function label(): string
    {
        return match($this) {
            self::SUPERADMIN => 'Super Admin',
            self::ADMIN => 'Admin Sistem',
            self::GURU => 'Guru Mapel',
            self::WALIKELAS => 'Wali Kelas',
            self::BENDAHARA => 'Bendahara',
            self::KEPSEK => 'Kepala Sekolah',
            self::SISWA => 'Siswa',
            self::ORANGTUA => 'Orang Tua',
            self::TATAUSAHA => 'Tata Usaha',
            self::BK => 'BK',
            self::PERPUS => 'Pustakawan',
        };
    }

    public function isInternal(): bool
    {
        return !in_array($this, [self::SISWA, self::ORANGTUA]);
    }

    public function isPortal(): bool
    {
        return in_array($this, [self::SISWA, self::ORANGTUA]);
    }

    /**
     * Get all internal (backend) role values.
     */
    public static function internalRoles(): array
    {
        return array_map(fn($r) => $r->value, array_filter(self::cases(), fn($r) => $r->isInternal()));
    }

    /**
     * Get all role values.
     */
    public static function allValues(): array
    {
        return array_map(fn($r) => $r->value, self::cases());
    }
}
