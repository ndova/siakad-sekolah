# Portal Orang Tua/Wali SIAKAD — UI/UX (Bagian 2)
## Presensi, Pembayaran, Alur Utama & Design System

---

## 3.7. PRESENSI

```
┌──────────────────────────────────────────────────────────────────┐
│  🕐 Presensi / Ahmad Fauzi — Semester Genap 2025/2026           │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌── REKAP MEI 2026 ──────────────────────────────────────────┐ │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │ │
│  │  │  ✅ 16   │  │  🟡  1   │  │  🟠  1   │  │  🔴  0   │   │ │
│  │  │  Hadir   │  │  Izin    │  │  Sakit   │  │  Alfa    │   │ │
│  │  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │ │
│  │  Kehadiran: ████████████████████░░ 88.9%                    │ │
│  │  Status: 🟡 Di bawah target (≥90%)                          │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌── KALENDER MEI 2026 ───────────────────────────────────────┐ │
│  │  [◄ April]  [Mei 2026]  [Juni ►]                           │ │
│  │  ┌───┬───┬───┬───┬───┬───┬───┐                             │ │
│  │  │Sen│Sel│Rab│Kam│Jum│Sab│Min│                             │ │
│  │  │   │   │   │   │   │   │ 1 │                             │ │
│  │  │ 2 │ 3 │ 4 │ 5 │ 6 │ 7 │ 8 │                             │ │
│  │  │ ✅│ ✅│ 🟡│ ✅│ ✅│   │   │  ← 4: Izin (surat dokter)  │ │
│  │  │ 9 │10 │11 │12 │13 │14 │15 │                             │ │
│  │  │ ✅│ ✅│ ✅│ 🟠│ ✅│   │   │  ← 12: Sakit               │ │
│  │  │16 │17 │18 │19 │20 │21 │22 │                             │ │
│  │  │ ✅│ ✅│ 🔴│ ✅│ ✅│   │   │  ← 18: Tanpa keterangan ⚠️ │ │
│  │  │23 │24 │25 │26 │27 │28 │29 │                             │ │
│  │  │ ✅│ ✅│   │   │   │   │   │                             │ │
│  │  └───┴───┴───┴───┴───┴───┴───┘                             │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌── REKAP PER SEMESTER ──────────────────────────────────────┐ │
│  │ Semester      │ Hadir │ Izin │ Sakit│ Alfa │ %            │ │
│  │ ──────────────┼───────┼──────┼──────┼──────┼────────────── │ │
│  │ Genap 2026    │  85   │  3   │  4   │  2   │ 90.4% 🟢     │ │
│  │ Ganjil 2025   │  88   │  2   │  2   │  0   │ 95.7% 🟢     │ │
│  │ Genap 2025    │  82   │  5   │  3   │  4   │ 87.2% 🟡     │ │
│  └───────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  📌 Notifikasi otomatis ke orang tua:                            │
│  ☑ Anak Alfa (tanpa keterangan) → notif real-time via WA/email  │
│  ☑ Keterlambatan >15 menit → notifikasi                         │
│  ☑ Laporan kehadiran bulanan → email otomatis tiap akhir bulan   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3.8. PEMBAYARAN

### 3.8.1. Tagihan Per Anak

```
┌──────────────────────────────────────────────────────────────────┐
│  💰 Pembayaran / Tagihan                                         │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌── RINGKASAN TOTAL ─────────────────────────────────────────┐ │
│  │ 👦 Ahmad (VII-A):  ⚠️ Rp 250.000  |  👧 Siti (X-TKJ):  ✅  │ │
│  │ ─────────────────────────────────────────────────────       │ │
│  │ TOTAL TUNGGAKAN: Rp 250.000                                 │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  [Tab anak: 👦 Ahmad Fauzi | 👧 Siti Rahma]                      │
│                                                                  │
│  ┌── AHMAD FAUZI ─────────────────────────────────────────────┐ │
│  │                                                             │ │
│  │  ⚠️ BELUM LUNAS                                           │ │
│  │  ┌────────────────────────────────────────────────────┐    │ │
│  │  │  SPP Bulan Mei 2026                                │    │ │
│  │  │  ───────────────────────────────────────           │    │ │
│  │  │  Nominal      : Rp 250.000                        │    │ │
│  │  │  Jatuh Tempo  : 25 Mei 2026 (3 hari lagi!)        │    │ │
│  │  │  Status       : ⚠️ BELUM DIBAYAR                  │    │ │
│  │  │  Denda        : Rp 5.000/hari (setelah jatuh tempo)│   │ │
│  │  │                                                   │    │ │
│  │  │  Rincian:                                         │    │ │
│  │  │  · SPP Pokok         Rp 200.000                  │    │ │
│  │  │  · Iuran Kegiatan    Rp  50.000                  │    │ │
│  │  │                                                   │    │ │
│  │  │  [💰 BAYAR SEKARANG]                              │    │ │
│  │  └────────────────────────────────────────────────────┘    │ │
│  │                                                             │ │
│  │  ✅ LUNAS                                                 │ │
│  │  ┌────────────────────────────────────────────────────┐    │ │
│  │  │ SPP April 2026 · Rp 250.000 · Lunas 15 Apr 2026   │    │ │
│  │  │ Metode: VA BNI · [📥 Kwitansi]                     │    │ │
│  │  ├────────────────────────────────────────────────────┤    │ │
│  │  │ SPP Maret 2026 · Rp 250.000 · Lunas 10 Mar 2026   │    │ │
│  │  │ Metode: QRIS · [📥 Kwitansi]                       │    │ │
│  │  └────────────────────────────────────────────────────┘    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  [Lihat Semua Riwayat →]                                        │
└──────────────────────────────────────────────────────────────────┘
```

### 3.8.2. Halaman Bayar (Flow)

```
┌──────────────────────────────────────────────────────────────────┐
│  💰 Bayar — SPP Mei 2026 (Ahmad Fauzi) · Rp 250.000             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Pilih Metode Pembayaran:                                        │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ 🏦 VA BNI│ │ 📱 QRIS  │ │ 🏧 Transfer│ │ 💳 E-Wallet│        │
│  │  (Otomatis)│ │ (Scan)  │ │ (Manual) │ │ (GoPay)  │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│                                                                  │
│  ┌── Virtual Account BNI ─────────────────────────────────────┐ │
│  │                                                            │ │
│  │  Nomor VA:    9889 2024 1001 0001                         │ │
│  │  Atas Nama:   SIAKAD SMPN 1 — Ahmad Fauzi                 │ │
│  │  Nominal:     Rp 250.000                                   │ │
│  │                                                            │ │
│  │  [📋 Salin Nomor VA]   [🔗 Buka m-Banking]                │ │
│  │                                                            │ │
│  │  ⏱️ Status: Menunggu Pembayaran                            │ │
│  │  (Terverifikasi otomatis 1-5 menit setelah transfer)       │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  📌 Setelah transfer, status langsung berubah LUNAS ✅           │
│  📌 Tidak perlu upload bukti (kecuali transfer manual)           │
└──────────────────────────────────────────────────────────────────┘
```

### 3.8.3. Riwayat Pembayaran Semua Anak

```
┌──────────────────────────────────────────────────────────────────┐
│  💰 Riwayat Pembayaran — Semua Anak                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  [Filter: Semua Anak ▼] [2026 ▼] [Semua Status ▼]               │
│                                                                  │
│  Tgl       │ Anak       │ Tagihan        │ Nominal   │ Status   │
│  ──────────┼────────────┼────────────────┼───────────┼──────────│
│  15 Apr    │ Ahmad F.   │ SPP April 2026 │ Rp 250rb  │ ✅ Lunas │
│  10 Apr    │ Siti R.    │ Iuran UKK      │ Rp 150rb  │ ✅ Lunas │
│  10 Mar    │ Ahmad F.   │ SPP Maret 2026 │ Rp 250rb  │ ✅ Lunas │
│  5 Mar     │ Siti R.    │ SPP Maret 2026 │ Rp 300rb  │ ✅ Lunas │
│  ...       │ ...        │ ...            │ ...       │ ...      │
│                                                                  │
│  Total dibayarkan 2026: Rp 2.500.000                             │
│  [📥 Download Laporan Tahunan (PDF)]                             │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3.9. PENGUMUMAN & PROFIL

**Pengumuman** — sama dengan portal siswa: list pengumuman dari admin/wali kelas, badge 🆕 untuk yang belum dibaca, urut kronologis terbalik.

**Profil Saya** (orang tua):
```
┌──────────────────────────────────────────────────────────────────┐
│  🧑 Profil Saya / Bpk. Budi Hartono                             │
├──────────────────────────────────────────────────────────────────┤
│  Data Diri:                                                      │
│  Nama: Budi Hartono | Email: budi@email.com                     │
│  No.HP: 0812-xxxx-xxxx (WA)                                     │
│  Alamat: Jl. Melati No. 5, Bandung                              │
│  [✏️ Edit Profil]                                                │
│                                                                  │
│  ┌── ANAK TERHUBUNG ──────────────────────────────────────────┐ │
│  │ 👦 Ahmad Fauzi — VII-A, SMPN 1 Bandung                     │ │
│  │    Hubungan: Ayah Kandung                                  │ │
│  │ 👧 Siti Rahma — X-TKJ, SMKN 2 Bandung                      │ │
│  │    Hubungan: Ayah Kandung                                  │ │
│  │ [➕ Tambah Anak] (masukkan NIS/NISN, verifikasi admin)      │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  Akun & Keamanan:                                                │
│  [🔒 Ubah Kata Sandi] [🔔 Atur Notifikasi]                      │
│                                                                  │
│  Notifikasi:                                                     │
│  ☑ WA: Tagihan, Alfa, Pengumuman penting                        │
│  ☑ Email: Rapor terbit, Laporan bulanan                         │
│  ☐ In-App: Semua notifikasi                                     │
└──────────────────────────────────────────────────────────────────┘
```

---

## 4. Alur Utama (User Journey)

```
┌────────────┐     ┌──────────────────┐     ┌────────────────────┐
│   LOGIN    │────▶│  DASHBOARD       │────▶│  PILIH ANAK        │
│ Email/HP   │     │  (semua anak)    │     │  (klik card/switcher)
│ + password │     │                  │     │                    │
└────────────┘     └──────────────────┘     └───────┬────────────┘
                                                    │
                               ┌────────────────────┼────────────────┐
                               ▼                    ▼                ▼
                        ┌───────────┐       ┌───────────┐    ┌─────────────┐
                        │ NILAI &   │       │ PRESENSI  │    │ PEMBAYARAN  │
                        │ RAPOR     │       │           │    │             │
                        └─────┬─────┘       └─────┬─────┘    └──────┬──────┘
                              │                   │                │
                              ▼                   ▼                ▼
                        ┌───────────┐       ┌───────────┐    ┌─────────────┐
                        │ Grafik    │       │ Kalender  │    │ Pilih       │
                        │ tren 3-4  │       │ bulanan   │    │ tagihan     │
                        │ semester  │       │ kode warna│    │ → metode    │
                        │           │       │           │    │ → bayar     │
                        │ ↓         │       │ ↓         │    │             │
                        │ Download  │       │ Lihat     │    │ ↓           │
                        │ rapor PDF │       │ rekap     │    │ LUNAS ✅    │
                        └───────────┘       └───────────┘    └─────────────┘
                              │
                              ▼
                        ┌───────────┐
                        │ P5 / PKL  │
                        │ Progress  │
                        │ Penilaian │
                        └───────────┘
```

### Narasi Alur:

**1. Login & Orientasi**
Orang tua login → Dashboard langsung menampilkan **card semua anak**. Sekilas terlihat mana yang nilainya turun, mana yang ada tunggakan. Tanpa klik apa pun, informasi kritis sudah terlihat.

**2. Pilih Anak & Lihat Detail**
Klik card anak atau switcher → semua konten berubah ke anak tersebut. Dashboard detail muncul: 6 card ringkasan.

**3. Pantau Nilai & Rapor**
Navigasi ke "Nilai & Rapor" → lihat **grafik tren 3-4 semester** (bukan hanya semester ini). Bisa lihat perbandingan vs semester lalu, mapel mana yang naik/turun. Download rapor digital PDF. **Ini halaman yang paling sering dikunjungi.**

**4. Pantau Presensi**
Lihat kalender presensi bulanan → langsung notice hari-hari Alfa/izin. Rekap per semester untuk melihat pola kehadiran jangka panjang.

**5. Pantau P5 / PKL**
Lihat progress projek P5 (timeline, penilaian dimensi PPP) atau untuk SMK: progress PKL (jurnal, penilaian).

**6. Bayar Tagihan** (aksi utama)
Dari dashboard atau menu Pembayaran → lihat tagihan per anak → pilih metode (VA BNI, QRIS, transfer, e-wallet) → dapat nomor VA / QR → bayar via m-banking → **status otomatis LUNAS** dalam 1-5 menit. Tidak perlu upload bukti manual.

**7. Unduh Kwitansi & Laporan**
Riwayat semua pembayaran → cetak/unduh kwitansi per transaksi atau laporan tahunan untuk keperluan pajak/pencatatan.

---

## 5. Design System & Komponen UI

### 5.1. Prinsip Desain

| Prinsip | Implementasi |
|---|---|
| **Scan-ability** | Card ringkasan dengan angka besar + kode warna — orang tua bisa baca sekilas tanpa klik |
| **Aksi jelas** | CTA selalu kontras (Bayar, Download, Lihat Detail) |
| **Warna informatif** | 🟢 Aman/Lunas/Tuntas, 🟡 Perhatian, 🔴 Kritis/Jatuh Tempo |
| **Konteks anak** | Child switcher selalu terlihat — tidak mungkin "tersesat" di anak yang salah |
| **Mobile-first** | PWA, touch-friendly, font besar untuk kemudahan baca |

### 5.2. Komponen Kunci

| Komponen | Fungsi & Posisi |
|---|---|
| **Child Switcher** | Dropdown/list di sidebar atas. Avatar + nama + kelas + jenjang. Indikator anak aktif. |
| **Card Ringkasan Anak** | Dashboard: 4 metrik kunci (nilai, hadir, tagihan, P5/PKL) per anak. Klik = ganti anak aktif. |
| **Card Metrik Besar** | Dashboard detail: angka besar + label + warna status. Contoh: "✅ 16" untuk presensi. |
| **Grafik Tren Nilai** | Line chart 3-4 semester, 3-4 mapel. Warna konsisten per mapel. Tooltip nilai detail. |
| **Progress Bar P5** | Bar horizontal + persentase + label tahap aktif. |
| **Kalender Presensi** | Grid 7 kolom, kode warna per status. Klik tanggal → popup detail. |
| **Tagihan Card** | Nominal + jatuh tempo + status badge. Expandable untuk rincian. |
| **Metode Pembayaran** | 4 card horizontal: VA, QRIS, Transfer, E-Wallet. Klik → instruksi detail. |
| **Tabel Responsif** | Untuk nilai & riwayat pembayaran. Kolom prioritas tetap terlihat di mobile. |
| **Notifikasi Badge** | Angka merah di bell icon top bar + di child card jika ada alerts. |

### 5.3. Warna Status

```
🟢 Hijau  #16A34A  — Lunas, Tuntas, Hadir, Progress on track
🟡 Amber  #D97706  — Jatuh tempo dekat, Remedial, Izin, Di bawah target
🔴 Merah  #DC2626  — Jatuh tempo lewat, Alfa, Nilai turun signifikan
🔵 Biru   #2563EB  — Informasi netral, link, aksi
⚪ Abu    #6B7280  — Data historis, tidak aktif
```

### 5.4. Ikhtisar Perbedaan Portal

| Aspek | Portal Siswa | Portal Orang Tua |
|---|---|---|
| **Login** | NIS + password | Email/HP + password |
| **Multi-anak** | Tidak | Ya (switcher) |
| **Dashboard** | 1 layar pribadi | Ringkasan semua anak + detail |
| **Ujian** | Mengerjakan CBT | Hanya lihat jadwal |
| **Nilai** | Lihat sendiri | Lihat + grafik tren + perbandingan |
| **Pembayaran** | Lihat tagihan sendiri | Bayar untuk SEMUA anak |
| **Presensi** | Lihat | Lihat + terima notifikasi Alfa |
| **Wali Kelas** | Tidak | Kontak langsung (WA/email) |
| **Rapor** | Download sendiri | Download untuk semua anak |
| **P5** | Lihat + tulis jurnal | Hanya pantau progress & nilai |

---

## 6. Tech Stack Rekomendasi (Frontend)

| Layer | Pilihan |
|---|---|
| **Framework** | Next.js 14+ (App Router) |
| **UI Library** | shadcn/ui + Tailwind CSS |
| **Icons** | Lucide React |
| **State** | Zustand (global: anak aktif, auth) + TanStack Query (data fetching) |
| **Charts** | Recharts (grafik tren nilai, progress P5) |
| **PWA** | next-pwa |
| **Payment** | Midtrans / Xendit (VA, QRIS, e-wallet) |
| **PWA Push** | Web Push API untuk notifikasi Alfa & tagihan |

---

Dokumen ini melengkapi **Bagian 1** (`03a-portal-ortu-bagian1.md`) yang mencakup struktur menu, layout, Dashboard, Detail Anak, Jadwal, Nilai & Rapor, dan P5.
