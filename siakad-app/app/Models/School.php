<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'npsn', 'address', 'phone', 'email', 'logo', 'is_active',
        // Tampilan login
        'portal_title', 'welcome_text', 'tagline', 'landing_image',
        'footer_text', 'primary_color', 'primary_color_light',
        'tempat_cetak',
        // Informasi tambahan
        'website', 'principal_name', 'accreditation',
        'established_year', 'vision', 'mission',
        // Pengaturan Rapor
        'rapor_tgl_otomatis', 'rapor_tanggal', 'rapor_bulan', 'rapor_tahun',
        'rapor_order_identitas', 'rapor_order_nilai', 'rapor_order_p5',
        'rapor_order_presensi', 'rapor_order_catatan', 'rapor_order_ttd',
        'rapor_show_nilai', 'rapor_show_p5', 'rapor_show_presensi',
        'rapor_show_catatan', 'rapor_show_ttd_ortu', 'rapor_show_ttd_walikelas',
        'rapor_show_ttd_kepsek',
        'rapor_label_nilai', 'rapor_label_p5', 'rapor_label_presensi',
        'rapor_label_catatan', 'rapor_label_ttd',
        // Kurikulum yang diaktifkan
        'kurikulum_kurmer_enabled', 'kurikulum_k13_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'established_year' => 'integer',
            'rapor_tgl_otomatis' => 'boolean',
            'rapor_tanggal' => 'integer',
            'rapor_bulan' => 'integer',
            'rapor_tahun' => 'integer',
            'rapor_order_identitas' => 'integer',
            'rapor_order_nilai' => 'integer',
            'rapor_order_p5' => 'integer',
            'rapor_order_presensi' => 'integer',
            'rapor_order_catatan' => 'integer',
            'rapor_order_ttd' => 'integer',
            'rapor_show_nilai' => 'boolean',
            'rapor_show_p5' => 'boolean',
            'rapor_show_presensi' => 'boolean',
            'rapor_show_catatan' => 'boolean',
            'rapor_show_ttd_ortu' => 'boolean',
            'rapor_show_ttd_walikelas' => 'boolean',
            'rapor_show_ttd_kepsek' => 'boolean',
            'kurikulum_kurmer_enabled' => 'boolean',
            'kurikulum_k13_enabled' => 'boolean',
        ];
    }
    public function academicYears(): HasMany { return $this->hasMany(AcademicYear::class); }
    public function majors(): HasMany { return $this->hasMany(Major::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
    public function feeTypes(): HasMany { return $this->hasMany(FeeType::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function staff(): HasMany { return $this->hasMany(Staff::class); }
    public function staffAttendances(): HasMany { return $this->hasMany(StaffAttendance::class); }
}
