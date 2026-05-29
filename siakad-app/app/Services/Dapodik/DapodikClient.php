<?php

namespace App\Services\Dapodik;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk koneksi ke API Dapodik.
 * Saat ini menggunakan endpoint yang dapat dikonfigurasi.
 */
class DapodikClient
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.dapodik.base_url', '');
        $this->apiKey  = config('services.dapodik.api_key');
    }

    /**
     * Cek apakah koneksi Dapodik tersedia.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }

    /**
     * Cari data sekolah berdasarkan NPSN dari API Dapodik.
     * Jika API tidak tersedia, kembalikan data default dari konfigurasi lokal.
     *
     * @return array{name: string, npsn: string, address: string, phone: string, email: string, website: string, principal_name: string, accreditation: string}|null
     */
    public function lookupSchool(string $npsn): ?array
    {
        // Coba panggil API Dapodik jika dikonfigurasi
        if ($this->isConfigured()) {
            return $this->fetchFromApi($npsn);
        }

        // Fallback: gunakan data referensi lokal
        return $this->lookupLocal($npsn);
    }

    /**
     * Panggil API Dapodik untuk mencari sekolah.
     */
    protected function fetchFromApi(string $npsn): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
            ])->get($this->baseUrl . '/sekolah/' . $npsn);

            if ($response->successful()) {
                $data = $response->json('data');
                return $this->normalizeSchoolData($data);
            }

            Log::warning('Dapodik API lookup gagal untuk NPSN: ' . $npsn, [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Dapodik API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Cek ketersediaan sekolah di database referensi Dapodik (local cache).
     */
    public function verifyNpsn(string $npsn): bool
    {
        // Cek format NPSN (8 digit)
        if (!preg_match('/^\d{8}$/', $npsn)) {
            return false;
        }

        return true;
    }

    /**
     * Lookup lokal — bisa di-extend dengan file referensi/seeder.
     */
    protected function lookupLocal(string $npsn): ?array
    {
        // Validasi format NPSN
        if (!$this->verifyNpsn($npsn)) {
            return null;
        }

        // Jika tidak ada data referensi, kembalikan null
        // (data sekolah harus diisi manual oleh admin)
        return null;
    }

    /**
     * Normalisasi data dari response API ke format yang seragam.
     */
    protected function normalizeSchoolData(?array $data): ?array
    {
        if (!$data) return null;

        return [
            'name'             => $data['nama_sekolah']    ?? $data['name']             ?? null,
            'npsn'             => $data['npsn']            ?? null,
            'address'          => $data['alamat']          ?? $data['address']          ?? null,
            'phone'            => $data['telepon']         ?? $data['phone']            ?? null,
            'email'            => $data['email']           ?? null,
            'website'          => $data['website']         ?? null,
            'principal_name'   => $data['kepala_sekolah']  ?? $data['principal_name']   ?? null,
            'accreditation'    => $data['akreditasi']      ?? $data['accreditation']    ?? null,
            'established_year' => $data['tahun_berdiri']   ?? $data['established_year'] ?? null,
        ];
    }
}
