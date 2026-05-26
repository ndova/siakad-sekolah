# Backend & Panel Internal — Bagian 1: Arsitektur, RBAC, Admin Sistem

> Fokus: Arsitektur backend Laravel + REST API + Panel Admin.

---

## 1. Arsitektur Backend

```
┌──────────────────────────────────────────────────────────────┐
│  Portal Siswa (React)  │  Portal Ortu (React)  │  Panel (React)│
└──────────┬─────────────────────┬─────────────────────┬────────┘
           │                     │                     │
     ┌─────┴─────┐         ┌─────┴─────┐         ┌─────┴─────┐
     │ Sanctum   │         │ Sanctum   │         │ Session   │
     │ Token     │         │ Token     │         │ Auth      │
     └─────┬─────┘         └─────┬─────┘         └─────┬─────┘
           │                     │                     │
           └─────────────────────┼─────────────────────┘
                                 │ HTTPS
                          ┌──────┴──────┐
                          │   Nginx     │
                          └──────┬──────┘
                                 │
              ┌──────────────────┼──────────────────┐
         ┌────┴────┐       ┌────┴────┐        ┌─────┴──────┐
         │ Laravel │       │  Redis  │        │ PostgreSQL │
         │  API    │       │ Cache/  │        │   16       │
         │  + Web  │       │ Queue   │        │            │
         └─────────┘       └─────────┘        └────────────┘

Eksternal: Midtrans/Xendit, Firebase FCM, SMTP, Dapodik API
```

### Stack Teknologi

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11+ (REST API + Web panel via Inertia.js/React) |
| Database | PostgreSQL 16 (JSONB untuk struktur CP/TP fleksibel) |
| Cache/Queue | Redis 7 (Horizon untuk job monitoring) |
| Auth API | Laravel Sanctum (token-based) |
| Auth Web | Laravel Session (panel internal) |
| PDF | DomPDF (rapor, kwitansi) |
| Excel | Laravel Excel (Maatwebsite) |
| Payment | Midtrans / Xendit SDK |
| Notifikasi | Firebase FCM + Laravel Notifications (email) |

### Struktur Direktori Kunci

```
app/
├── Http/
│   ├── Controllers/Api/     ← REST API untuk portal siswa & ortu
│   ├── Controllers/Web/     ← Panel internal (Inertia.js)
│   └── Resources/           ← JsonResource (transformasi response API)
├── Models/
│   ├── Akademik/            ← Kurikulum, CP, TP, ATP, Mapel, Rombel
│   ├── Keuangan/            ← Tagihan, Transaksi
│   ├── Penilaian/           ← Nilai, Rapor, P5
│   └── Ujian/               ← BankSoal, PaketUjian, SesiUjian
├── Services/                ← Business logic layer
│   ├── PenilaianService, KeuanganService, UjianService, RaporService
├── Jobs/                    ← Queue jobs (generate rapor PDF, notifikasi masal)
└── Policies/                ← Otorisasi per role & model
```

---

## 2. Role-Based Access Control (RBAC)

### 11 Role Internal

| # | Role | Kode | Tugas Utama |
|---|---|---|---|
| 1 | Super Admin | `super_admin` | Akses penuh, multi-tenant |
| 2 | Admin Sekolah | `admin` | Data master, user, konfigurasi |
| 3 | Waka Kurikulum | `waka_kurikulum` | CP/TP/ATP, jadwal, kalender akademik |
| 4 | **Guru Mapel** | `guru` | Input nilai, bank soal, ujian, P5 |
| 5 | **Wali Kelas** | `wali_kelas` | Rekap rombel, validasi rapor, presensi |
| 6 | Koordinator P5 | `koordinator_p5` | Tema & modul P5, asesmen |
| 7 | **Bendahara** | `bendahara` | Biaya, tagihan, transaksi, laporan |
| 8 | **Kepala Sekolah** | `kepsek` | Dashboard, semua laporan |
| 9 | Guru BK | `guru_bk` | Konseling, pelanggaran |
| 10 | Waka Kesiswaan | `waka_kesiswaan` | Ekskul, OSIS |
| 11 | Kaprodi (SMK) | `kaprodi` | PKL, UKK, sertifikasi |

### Matriks Izin Ringkas

> ✅ = full | 👁 = read-only | ◐ = data sendiri/rombelnya | — = no access

| Modul | Admin | Guru | Wali Kelas | Bendahara | Kepsek |
|---|---|---|---|---|---|
| User & Role | ✅ | — | — | — | 👁 |
| Data Master (sekolah, rombel, mapel) | ✅ | 👁 | 👁 | — | 👁 |
| Kurikulum (CP/TP/ATP) | ✅ | 👁 | 👁 | — | 👁 |
| Input Nilai | — | ◐ | 👁 | — | 👁 |
| Rapor (validasi) | — | — | ◐ | — | ✅ |
| Bank Soal & Ujian | — | ◐ | — | — | 👁 |
| Projek P5 | — | ◐ | 👁 | — | 👁 |
| Presensi | — | ◐ | ✅ | — | 👁 |
| Pembayaran & Keuangan | 👁 | — | 👁 | ✅ | ✅ |
| Laporan & Analitik | ✅ | — | 👁 | ✅ | ✅ |

---

## 3. Struktur REST API

### Konvensi

```
Base URL:    https://api.siakad.example.com/v1
Auth:        Bearer Token (Sanctum)
Format:      JSON, dibungkus JsonResource
```

### Namespace API

```
/api/v1/
├── auth/                    # Login, logout, forgot password
├── me/                      # Profil & preferensi user login
├── announcements/           # Pengumuman
├── akademik/
│   ├── jadwal/              # Jadwal siswa
│   ├── mapel/               # Mata pelajaran
│   └── kurikulum/           # CP/TP/ATP (read-only)
├── penilaian/
│   ├── nilai/               # Nilai per TP per mapel
│   ├── rapor/               # Rapor digital + download PDF
│   └── p5/                  # Projek P5
├── presensi/                # Riwayat & rekap presensi
├── ujian/                   # Paket, sesi, & hasil ujian online
├── keuangan/
│   ├── tagihan/             # Tagihan per siswa
│   ├── transaksi/           # Riwayat pembayaran
│   └── pembayaran/          # Initiate pembayaran (VA, QRIS, dll)
└── children/                # [Ortu] data tiap anak
```

### Perbedaan Web Routes vs API Routes

| | Web Routes (`/panel/...`) | API Routes (`/api/v1/...`) |
|---|---|---|
| Pengguna | Internal (admin, guru, bendahara, dll) | Portal siswa & ortu |
| Auth | Session-based | Token-based (Sanctum) |
| Output | Inertia.js page (React) | JSON |
| Scope Data | Semua data sekolah | Hanya milik user sendiri |
| Rate Limit | 300/min | 60/min |

### Aliran Data: Backend → API → Portal

```
PANEL INTERNAL (INPUT)        BACKEND               PORTAL (READ)
─────────────────────    ───────────────    ──────────────────────────
Guru input nilai      →  Validasi + simpan → API tampilkan di portal siswa
                          + invalidate cache
Bendahara generate    →  Buat row tagihan   → API tampilkan di portal ortu
  tagihan SPP            + kirim notifikasi
Wali kelas validasi   →  Lock rapor         → API sediakan download PDF
  rapor                   + job generate PDF
Admin input data      →  Simpan data        → API sediakan data referensi
  master                 master
```

---

## 4. Panel Admin Sistem

### 4.1 Menu Admin

```
🏠 Dashboard Admin
├── Card: total siswa, guru, rombel, status kelengkapan data
├── Quick Actions: tambah siswa, tambah guru, kenaikan kelas, export Dapodik
└── Log aktivitas terkini (siapa melakukan apa, kapan)

👥 Manajemen Pengguna
├── Daftar User (semua role) — filter role, status, search
├── Tambah User — nama, email, NIP, role, assign mapel/rombel
├── Role & Permission — lihat/edit matriks izin
└── Log Aktivitas — audit trail

🏫 Data Master Sekolah
├── Identitas Sekolah — NPSN, nama, alamat, logo, kop surat
├── Tahun Ajaran — tambah, aktifkan, arsipkan
├── Semester — gasal/genap, tanggal mulai/selesai
└── Kalender Akademik — tambah event/libur nasional

📚 Data Akademik
├── Rombongan Belajar — CRUD, assign wali kelas, lihat anggota
├── Mata Pelajaran — CRUD, kelompok (umum/kejuruan/muatan lokal/P5)
├── Kurikulum & CP/TP/ATP — tree hierarchy:
│   └── Mapel → Fase → Elemen → CP → TP → ATP
├── KKTP — interval nilai & deskripsi per mapel
└── Mapping Mapel ↔ Rombel — mapel mana diajarkan di kelas mana

👨‍🏫 Data GTK (Guru & Tendik)
├── Daftar Guru — lihat, edit, status
├── Assign Wali Kelas — guru → rombel
└── Assign Guru Mapel — guru → mapel + rombel

👨‍🎓 Data Siswa
├── Daftar Siswa — search, filter kelas, status aktif/alumni/mutasi
├── Data Orang Tua/Wali — per siswa
├── Mutasi — masuk/keluar dengan tanggal & alasan
├── Kenaikan Kelas — wizard masal per rombel
└── Alumni — kelulusan

⚙️ Konfigurasi
├── Template Rapor — upload template PDF Kemendikbudristek
├── Payment Gateway — API Key, mode sandbox/production
├── Notifikasi — konfigurasi FCM, SMTP, template WA
└── Backup & Restore — jadwal otomatis + manual download

📤 Export & Import
├── Import Siswa (Excel template)
├── Import GTK (Excel template)
└── Export Dapodik (format Kemendikbudristek)
```

### 4.2 Halaman Unggulan Admin

#### Manajemen Kurikulum (CP/TP/ATP)

Tampilan tree hierarchy untuk Kurikulum Merdeka:

```
🌳 Kurikulum Merdeka — Tahun 2025/2026 — Semester Ganjil
│
├── 📘 Bahasa Indonesia (Fase D — Kelas VII)
│   ├── Elemen: Menyimak
│   │   └── CP: Peserta didik mampu menganalisis informasi...
│   │       ├── TP-01: Mengidentifikasi informasi dalam teks laporan
│   │       ├── TP-02: Menganalisis struktur teks laporan hasil observasi
│   │       └── TP-03: Mengevaluasi keakuratan informasi...
│   ├── Elemen: Membaca dan Memirsa ──► CP → TP
│   └── Elemen: Berbicara dan Mempresentasikan ──► CP → TP
│
├── 📗 Matematika (Fase D — Kelas VII)
└── 📙 IPA (Fase D — Kelas VII)
```

Setiap node clickable untuk edit, tambah child, atau hapus.

#### Kenaikan Kelas (Wizard)

```
Step 1: Pilih Tahun Ajaran → 2025/2026
Step 2: Pilih Rombel Asal → Kelas 7A (35 siswa)
Step 3: Pilih Rombel Tujuan → Kelas 8A
Step 4: Konfirmasi → Tabel preview: 35 siswa akan naik ke 8A
Step 5: Proses → [Konfirmasi Kenaikan Kelas]
```

Siswa yang tidak memenuhi syarat naik (nilai merah) ditandai ⚠️ dan bisa dipilih manual (naik/tinggal).

---

## 5. Ringkasan Backend → API → Portal

| No | Data diinput di Backend oleh | Endpoint API yang dikonsumsi | Ditampilkan di Portal |
|---|---|---|---|
| 1 | Admin: data master sekolah | `GET /api/v1/me/profile` | Profil siswa/ortu (nama sekolah) |
| 2 | Admin/Waka: jadwal pelajaran | `GET /api/v1/akademik/jadwal` | Jadwal siswa |
| 3 | Admin/Waka: CP/TP/ATP | `GET /api/v1/akademik/kurikulum` | Referensi TP di halaman nilai |
| 4 | Guru: input nilai per TP | `GET /api/v1/penilaian/nilai` | Nilai & Rapor siswa + ortu |
| 5 | Guru: bank soal & paket ujian | `GET /api/v1/ujian/paket` | Daftar ujian + tampilan CBT |
| 6 | Wali kelas: validasi rapor | `GET /api/v1/penilaian/rapor` | Download rapor PDF |
| 7 | Bendahara: generate tagihan | `GET /api/v1/keuangan/tagihan` | Status pembayaran ortu |
| 8 | Bendahara: verifikasi bayar | `POST /api/v1/keuangan/pembayaran` | Ortu melakukan pembayaran |

**Kunci**: Backend = sumber kebenaran data (write). API = jembatan read-only ke portal. Portal tidak pernah menulis langsung — semua input melalui panel internal.
