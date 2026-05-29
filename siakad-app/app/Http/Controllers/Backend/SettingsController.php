<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\ExportDapodikJob;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Services\Dapodik\DapodikClient;
use App\Services\SchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan sekolah.
     */
    public function edit()
    {
        $school = SchoolService::get() ?? new School();
        return view('backend.settings.edit', compact('school'));
    }

    /**
     * Simpan pengaturan sekolah.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Identitas sekolah
            'name'             => 'required|string|max:200',
            'npsn'             => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:100',
            'website'          => 'nullable|string|max:200',
            'principal_name'   => 'nullable|string|max:100',
            'accreditation'    => 'nullable|string|max:20',
            'established_year' => 'nullable|integer|min:1900|max:2100',
            'vision'           => 'nullable|string',
            'mission'          => 'nullable|string',

            // Tampilan login
            'portal_title'        => 'nullable|string|max:200',
            'welcome_text'        => 'nullable|string|max:255',
            'tagline'             => 'nullable|string',
            'footer_text'         => 'nullable|string|max:255',
            'primary_color'       => 'nullable|string|max:20',
            'primary_color_light' => 'nullable|string|max:20',
            'tempat_cetak'        => 'nullable|string|max:100',

            // Pengaturan Rapor
            'rapor_tgl_otomatis'     => 'nullable|boolean',
            'rapor_tanggal'          => 'nullable|integer|min:1|max:31',
            'rapor_bulan'            => 'nullable|integer|min:1|max:12',
            'rapor_tahun'            => 'nullable|integer|min:2000|max:2100',
            'rapor_order_identitas'  => 'nullable|integer|min:1|max:6',
            'rapor_order_nilai'      => 'nullable|integer|min:1|max:6',
            'rapor_order_p5'         => 'nullable|integer|min:1|max:6',
            'rapor_order_presensi'   => 'nullable|integer|min:1|max:6',
            'rapor_order_catatan'    => 'nullable|integer|min:1|max:6',
            'rapor_order_ttd'        => 'nullable|integer|min:1|max:6',
            'rapor_show_nilai'       => 'nullable|boolean',
            'rapor_show_p5'          => 'nullable|boolean',
            'rapor_show_presensi'    => 'nullable|boolean',
            'rapor_show_catatan'     => 'nullable|boolean',
            'rapor_show_ttd_ortu'    => 'nullable|boolean',
            'rapor_show_ttd_walikelas'=> 'nullable|boolean',
            'rapor_show_ttd_kepsek'  => 'nullable|boolean',
            'rapor_label_nilai'      => 'nullable|string|max:50',
            'rapor_label_p5'         => 'nullable|string|max:50',
            'rapor_label_presensi'   => 'nullable|string|max:50',
            'rapor_label_catatan'    => 'nullable|string|max:50',
            'rapor_label_ttd'        => 'nullable|string|max:60',
            'kurikulum_kurmer_enabled' => 'nullable|boolean',
            'kurikulum_k13_enabled'    => 'nullable|boolean',

            // Upload
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'landing_image' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:5120',
        ]);

        $school = SchoolService::get() ?? new School();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($school->logo) Storage::disk('public')->delete($school->logo);
            $validated['logo'] = $request->file('logo')->store('school', 'public');
        }

        // Handle landing image upload
        if ($request->hasFile('landing_image')) {
            if ($school->landing_image) Storage::disk('public')->delete($school->landing_image);
            $validated['landing_image'] = $request->file('landing_image')->store('school', 'public');
        }

        // Deteksi NPSN baru atau berubah
        $oldNpsn = $school->npsn;
        $newNpsn = $validated['npsn'] ?? null;
        $npsnChanged = $oldNpsn !== $newNpsn;

        // Jika NPSN diisi/berubah, coba lookup data sekolah dari Dapodik
        $dapodikData = null;
        if ($npsnChanged && $newNpsn) {
            $dapodik = new DapodikClient();
            $dapodikData = $dapodik->lookupSchool($newNpsn);

            // Auto-fill data sekolah yang masih kosong dari hasil lookup Dapodik
            if ($dapodikData) {
                $validated = array_merge($validated, array_filter($dapodikData, fn($v) => !is_null($v)));
            }
        }

        $school->fill($validated);
        $school->is_active = true;
        $school->save();

        SchoolService::clearCache();

        // Auto-sync ke Dapodik jika NPSN baru diisi atau diubah
        if ($npsnChanged && $newNpsn) {
            $this->dispatchDapodikSync($school->id);
        }

        $msg = 'Pengaturan sekolah berhasil disimpan.';
        if ($dapodikData) {
            $filledFields = array_keys(array_filter($dapodikData));
            $msg .= ' Data sekolah (' . implode(', ', $filledFields) . ') berhasil dilengkapi dari Dapodik.';
        }
        if ($npsnChanged && $newNpsn) {
            $msg .= ' Sinkronisasi data ke Dapodik sedang diproses.';
        }

        return redirect()->route('backend.settings.edit')
            ->with('success', $msg);
    }

    /**
     * Dispatch Dapodik export jobs untuk seluruh data sekolah.
     */
    protected function dispatchDapodikSync(string $schoolId): void
    {
        $classIds = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->pluck('id');

        if ($classIds->isEmpty()) return;

        $semesterId = Semester::whereHas('academicYear',
            fn($q) => $q->where('school_id', $schoolId)
        )->where('is_active', true)->value('id');

        if (!$semesterId) return;

        $userId = auth()->id();

        // Export 3 tipe entitas ke Dapodik
        ExportDapodikJob::dispatch('student', $classIds, $semesterId, $userId);
        ExportDapodikJob::dispatch('teacher', $classIds, $semesterId, $userId);
        ExportDapodikJob::dispatch('grade',   $classIds, $semesterId, $userId);
    }

    /**
     * API: Get school settings (public).
     */
    public function apiShow()
    {
        $school = SchoolService::get();
        if (!$school) {
            return response()->json(['message' => 'No school configured'], 404);
        }

        return response()->json([
            'data' => [
                'name'               => $school->name,
                'npsn'               => $school->npsn,
                'address'            => $school->address,
                'phone'              => $school->phone,
                'email'              => $school->email,
                'website'            => $school->website,
                'principal_name'     => $school->principal_name,
                'accreditation'      => $school->accreditation,
                'established_year'   => $school->established_year,
                'vision'             => $school->vision,
                'mission'            => $school->mission,
                'portal_title'       => $school->portal_title,
                'welcome_text'       => $school->welcome_text,
                'tagline'            => $school->tagline,
                'footer_text'        => $school->footer_text,
                'primary_color'      => $school->primary_color,
                'primary_color_light'=> $school->primary_color_light,
                'tempat_cetak'       => $school->tempat_cetak,
                'logo_url'           => $school->logo ? asset('storage/' . $school->logo) : null,
                'landing_image_url'  => $school->landing_image ? asset('storage/' . $school->landing_image) : null,
            ],
        ]);
    }
}
