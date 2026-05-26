# Backend & Panel Internal — Bagian 3: Bendahara, Kepala Sekolah & Interaksi API

> Fokus: Panel Bendahara (keuangan), Kepala Sekolah (dashboard eksekutif), dan penjelasan interaksi Backend ↔ REST API ↔ Portal depan.

---

## Daftar Isi

- [1. Bendahara — Panel Keuangan](#1-bendahara--panel-keuangan)
- [2. Kepala Sekolah — Dashboard Eksekutif](#2-kepala-sekolah--dashboard-eksekutif)
- [3. Interaksi Backend ↔ REST API ↔ Portal Depan](#3-interaksi-backend--rest-api--portal-depan)

---

## 1. Bendahara — Panel Keuangan

### 1.1 Menu Bendahara

```
🏠 Dashboard Keuangan
├── Card: total tagihan, total terbayar, total tunggakan, % koleksi
├── Grafik: pemasukan per bulan, per jenis biaya
└── Quick Actions: generate tagihan, input pembayaran manual

💰 Master Biaya
├── Jenis Biaya — SPP, Ujian, Kegiatan, Praktikum, P5, dll
├── Komponen Biaya — breakdown per jenis
├── Tarif per Kelas/Jurusan — nominal berbeda per jenjang
└── Diskon & Keringanan — beasiswa, subsidi

📋 Tagihan
├── Daftar Tagihan — filter: status, jenis, kelas, tanggal
├── Generate Tagihan — masal per jenis biaya + kelas
├── Tagihan Perorangan — buat tagihan khusus satu siswa
├── Kirim Notifikasi — kirim reminder via WA/email
└── Jatuh Tempo — monitor tagihan yang akan/segera jatuh tempo

💳 Transaksi
├── Daftar Transaksi — semua pembayaran masuk
├── Input Pembayaran Manual — untuk transfer/tunai
├── Verifikasi Pembayaran — jika manual (upload bukti)
├── Riwayat Transaksi per Siswa
└── Pembatalan Transaksi (refund)

📊 Laporan Keuangan
├── Arus Kas — pemasukan per periode
├── Rekap per Jenis Biaya
├── Tunggakan per Siswa / per Kelas
├── Laporan Bulanan/Tahunan
├── Ekspor Excel / PDF
└── Grafik & Dashboard Analitik

⚙️ Konfigurasi
├── Payment Gateway — integrasi Midtrans/Xendit
├── Nomor Rekening Sekolah — untuk transfer manual
├── Template Kwitansi — custom header/footer
└── Akun Bank — daftar rekening aktif
```

### 1.2 Dashboard Keuangan

```
┌──────────────────────────────────────────────────────────────┐
│ Dashboard Keuangan                    Tahun Ajaran 2025/2026  │
│ Semester Genap                       Periode: Jan-Jun 2026   │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ │
│ │ Rp 42.5M  │ │ Rp 38.2M  │ │ Rp 4.3M   │ │ 89.9%     │ │
│ │ Total      │ │ Terbayar   │ │ Tunggakan  │ │ Koleksi   │ │
│ │ Tagihan    │ │            │ │            │ │           │ │
│ └────────────┘ └────────────┘ └────────────┘ └────────────┘ │
│                                                              │
│ ┌─────────────────────────────┐ ┌──────────────────────────┐ │
│ │ Pemasukan Bulanan (grafik)  │ │ Komposisi per Jenis      │ │
│ │ Jan ████████████ 8.2M       │ │ SPP     ████████ 52%     │ │
│ │ Feb ██████████ 7.5M         │ │ Kegiatan██████ 28%      │ │
│ │ Mar ███████████ 8.5M        │ │ Ujian   ████ 12%        │ │
│ │ Apr █████████ 7.0M          │ │ Lain     ██ 8%          │ │
│ │ Mei ███████ 7.0M (ongoing)  │ │                          │ │
│ └─────────────────────────────┘ └──────────────────────────┘ │
│                                                              │
│ ┌──────────────────────────────────────────────┐             │
│ │ ⚠️ Perhatian                                  │             │
│ │ • 12 tagihan SPP jatuh tempo minggu ini       │             │
│ │ • Kelas 9C: tunggakan tertinggi (Rp 1.2M)     │             │
│ │ • 3 transaksi menunggu verifikasi manual       │             │
│ └──────────────────────────────────────────────┘             │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 1.3 Generate Tagihan Masal

**Wizard Generate Tagihan:**

```
Step 1: Pilih Jenis Biaya
        [✓] SPP Bulanan — Rp 350.000/siswa/bulan
        [ ] Ujian Semester — Rp 150.000/siswa/semester
        [ ] Kegiatan Class Meeting — Rp 50.000/siswa

Step 2: Pilih Kelas & Periode
        Kelas: [✓ 7A] [✓ 7B] [✓ 7C] [✓ 8A] [✓ 8B] [✓ 9A] [✓ 9B]
        Periode: Juni 2026
        Jatuh Tempo: [10 Juni 2026]

Step 3: Preview
        ┌──────────────────────────────────────────────┐
        │ Total siswa: 245                             │
        │ Tagihan per siswa: Rp 350.000                 │
        │ Total tagihan: Rp 85.750.000                  │
        │                                               │
        │ 3 siswa punya beasiswa (diskon 50%)           │
        │ 2 siswa bebas SPP (yatim/piatu)               │
        └──────────────────────────────────────────────┘

Step 4: Konfirmasi
        [✓ Generate Tagihan & Kirim Notifikasi]
```

Setelah generate:
- Job queue: buat row tagihan per siswa
- Job queue: kirim notifikasi WA/email ke orang tua
- Status: "Belum Lunas" → tampil di portal ortu

### 1.4 Daftar Tagihan & Status

```
┌──────────────────────────────────────────────────────────────┐
│ Daftar Tagihan — SPP Bulanan — Juni 2026                     │
├──────────────────────────────────────────────────────────────┤
│ Filter: [Kelas ▼] [Status ▼] [Cari Nama/NIS...]              │
│                                                              │
│ ┌──────┬──────────┬───────┬──────────┬──────────┬──────────┐ │
│ │ No   │ Nama     │ Kelas │ Jumlah   │ Status   │ Jatuh    │ │
│ │      │          │       │          │          │ Tempo    │ │
│ ├──────┼──────────┼───────┼──────────┼──────────┼──────────┤ │
│ │ 1    │ Adi      │ 7A    │ 350.000  │ 🟢 Lunas │ 10 Jun   │ │
│ │ 2    │ Budi     │ 7A    │ 350.000  │ 🔴 Blm   │ 10 Jun   │ │
│ │ 3    │ Citra    │ 7A    │ 175.000  │ 🟡 Sbsn  │ 10 Jun   │ │
│ │ 4    │ Dina     │ 7B    │ 350.000  │ 🟢 Lunas │ 10 Jun   │ │
│ └──────┴──────────┴───────┴──────────┴──────────┴──────────┘ │
│                                                              │
│ 🟢 Lunas  🔴 Belum Lunas  🟡 Diskon/Subsidi  ⚠️ Jatuh Tempo │
│                                                              │
│ [📩 Kirim Reminder Tunggakan]  [📥 Ekspor Excel]             │
└──────────────────────────────────────────────────────────────┘
```

### 1.5 Verifikasi Pembayaran Manual

```
┌──────────────────────────────────────────────────────────────┐
│ Verifikasi Pembayaran                                        │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ Menunggu Verifikasi: 3 transaksi                             │
│                                                              │
│ ┌──────┬──────────┬──────────┬──────────┬──────────────────┐ │
│ │ No   │ Siswa    │ Jumlah   │ Tgl      │ Bukti           │ │
│ ├──────┼──────────┼──────────┼──────────┼──────────────────┤ │
│ │ 1    │ Fani     │ 350.000  │ 5/6      │ [📷 Lihat Bukti] │ │
│ │ 2    │ Gilang   │ 500.000  │ 6/6      │ [📷 Lihat Bukti] │ │
│ │ 3    │ Hani     │ 175.000  │ 6/6      │ [📷 Lihat Bukti] │ │
│ └──────┴──────────┴──────────┴──────────┴──────────────────┘ │
│                                                              │
│ Pilih transaksi → [✅ Verifikasi] [❌ Tolak] [+ Catatan]     │
│                                                              │
│ Verifikasi → status tagihan jadi "Lunas" → muncul di portal  │
└──────────────────────────────────────────────────────────────┘
```

### 1.6 Laporan Keuangan

**Fitur Laporan:**
- **Arus Kas**: pemasukan per hari/minggu/bulan/tahun
- **Rekap per Jenis Biaya**: SPP, ujian, kegiatan, dll
- **Tunggakan**: per siswa, per kelas — aging (30/60/90+ hari)
- **Rekap Pembayaran per Channel**: VA, QRIS, transfer, e-wallet, tunai
- **Ekspor**: Excel (detail) dan PDF (ringkasan untuk kepsek/yayasan)

---

## 2. Kepala Sekolah — Dashboard Eksekutif

### 2.1 Menu Kepala Sekolah

```
🏠 Dashboard Eksekutif

📊 Akademik
├── Ringkasan Nilai — rata-rata per mapel, per kelas
├── Tingkat Kelulusan — % siswa tuntas
├── Prestasi Siswa — akademik & non-akademik
└── Perkembangan Kurikulum — progres CP/TP tercapai

📋 Rapor
├── Monitoring Validasi — siapa sudah/belum divalidasi
├── Lihat Rapor per Siswa (read-only)
└── Tanda Tangan Digital Rapor (final approval)

👨‍🏫 Guru & Tendik
├── Kinerja Guru — rekap input nilai, kehadiran mengajar
├── Kehadiran Guru — presensi GTK
└── Beban Mengajar — jam mengajar per guru

💰 Keuangan
├── Ringkasan Keuangan — pemasukan, pengeluaran, saldo
├── Tunggakan — ringkasan per kelas
└── Laporan Keuangan — lihat & download

📈 Analitik & Laporan
├── Dashboard Analitik — grafik tren akademik & keuangan
├── Laporan Akademik — per semester, per tahun
├── Laporan Keuangan — per bulan, per tahun
└── Ekspor Semua Laporan

📢 Komunikasi
├── Pengumuman — buat pengumuman seluruh sekolah
└── Notifikasi — kirim ke guru/siswa/ortu
```

### 2.2 Dashboard Kepala Sekolah

```
┌──────────────────────────────────────────────────────────────┐
│ Dashboard Kepala Sekolah              Tahun 2025/2026 Genap  │
│ SMP Negeri 1 ...                                  👤 Kepsek  │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│  │ 📊       │ │ 👨‍🎓      │ │ 👨‍🏫      │ │ 💰       │        │
│  │ 87.5%   │ │ 350      │ │ 42       │ │ 89.9%    │        │
│  │ Rata²    │ │ Siswa    │ │ GTK      │ │ Koleksi  │        │
│  │ Nilai    │ │ Aktif    │ │ Aktif    │ │ Keuangan │        │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘        │
│                                                              │
│  ┌──────────────────────────┐ ┌──────────────────────────┐   │
│  │ 📊 Tren Nilai per Mapel  │ │ 💰 Pemasukan Bulanan     │   │
│  │ (grafik garis multi)     │ │ (grafik batang)           │   │
│  │ B.Indo: 85→87→88↗       │ │ Jan: 8.2M                │   │
│  │ Mat: 72→70→68↘          │ │ Feb: 7.5M                │   │
│  │ IPA: 80→82→84↗          │ │ Mar: 8.5M                │   │
│  └──────────────────────────┘ └──────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────┐ ┌──────────────────────────┐   │
│  │ ⚠️ Perhatian              │ │ ✅ Status Validasi Rapor │   │
│  │ • 8B: Rata² nilai turun  │ │ 7A: ✅ 35/35 (100%)     │   │
│  │ • 3 siswa Alfa >5x       │ │ 7B: ⚠️ 28/35 (80%)     │   │
│  │ • 2 guru blm input nilai │ │ 7C: ✅ 35/35 (100%)     │   │
│  │ • Tunggakan 9C: Rp 1.2M │ │ ...                       │   │
│  └──────────────────────────┘ └──────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────────────────────────────┐        │
│  │ Quick Actions                                     │        │
│  │ [📊 Lihat Laporan Lengkap] [📢 Buat Pengumuman]   │        │
│  │ [📥 Download Rekap Akademik] [📥 Download Lap.Keu] │        │
│  └──────────────────────────────────────────────────┘        │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 2.3 Fitur Kunci Kepala Sekolah

**1. Monitoring Validasi Rapor:**
- Progress bar per rombel: berapa siswa sudah divalidasi wali kelas
- Kepsek bisa melihat rapor (read-only) per siswa
- **Tanda Tangan Digital** — kepsek approve final seluruh rapor → rapor terkunci permanen → muncul di portal

**2. Analitik Akademik:**
- Grafik tren rata-rata nilai per mapel (3-4 semester)
- Heatmap: kelas mana yang perlu perhatian
- Perbandingan semester gasal vs genap
- Identifikasi mapel dengan tingkat remedial tinggi

**3. Monitoring Kinerja Guru:**
- Status input nilai: siapa sudah/belum
- Jumlah ujian online yang dibuat & dilaksanakan
- Kehadiran mengajar (jika ada presensi guru)

---

## 3. Interaksi Backend ↔ REST API ↔ Portal Depan

### 3.1 Arsitektur Data Flow Lengkap

```
┌─────────────────────────────────────────────────────────────────────┐
│                    SIKLUS DATA SIAKAD                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   ┌──────────────────────────────────────────────┐                  │
│   │           PANEL INTERNAL (WEB ROUTES)         │                  │
│   │                                              │                  │
│   │  Admin ──────► data master ──────────┐       │                  │
│   │  Guru ───────► nilai, soal, ujian ───┤       │                  │
│   │  Wali Kelas ─► validasi rapor, sikap ─┤      │                  │
│   │  Bendahara ──► tagihan, transaksi ────┤      │                  │
│   │  Kepsek ─────► (read-only semua) ─────┘      │                  │
│   └────────────────────────┬─────────────────────┘                  │
│                            │                                        │
│                     ┌──────┴──────┐                                  │
│                     │  DATABASE   │                                  │
│                     │ PostgreSQL  │                                  │
│                     └──────┬──────┘                                  │
│                            │                                        │
│              ┌─────────────┼─────────────┐                          │
│              │             │             │                          │
│        ┌─────┴─────┐ ┌────┴─────┐ ┌─────┴──────┐                    │
│        │  Cache    │ │  Queue   │ │  Storage   │                    │
│        │  Redis    │ │  Redis   │ │  S3/Local  │                    │
│        └─────┬─────┘ └──────────┘ └────────────┘                    │
│              │                                                      │
│     ┌────────┴────────┐                                             │
│     │  REST API (v1)  │  ◄── JsonResource + Sanctum Token           │
│     │  Laravel        │                                             │
│     └────────┬────────┘                                             │
│              │                                                      │
│     ┌────────┴────────┐                                             │
│     │  Response JSON  │                                             │
│     └────────┬────────┘                                             │
│              │                                                      │
│   ┌──────────┼──────────┐                                          │
│   │          │          │                                          │
│ ┌─┴──┐   ┌──┴──┐   ┌───┴───┐                                      │
│ │Siswa│  │Ortu │   │Mobile │   ◄── Semua READ (GET)                │
│ │SPA  │  │SPA  │   │App    │       + POST untuk bayar & ujian      │
│ └────┘   └─────┘   └───────┘                                      │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Tabel Lengkap: Backend Input → API Endpoint → Portal Output

| # | Backend Panel | Siapa Input | Endpoint API | Method | Portal | Data yang Ditampilkan |
|---|---|---|---|---|---|---|
| 1 | Data Master → Identitas Sekolah | Admin | `GET /api/v1/me/school` | GET | Siswa, Ortu | Nama sekolah, logo, alamat di header portal |
| 2 | Data Master → Siswa | Admin | `GET /api/v1/me/profile` | GET | Siswa | NIS, nama, kelas, wali kelas |
| 3 | Data Master → Siswa | Admin | `GET /api/v1/children` | GET | Ortu | Daftar anak (nama, NIS, kelas) |
| 4 | Data Master → Rombel | Admin | `GET /api/v1/me/class` | GET | Siswa, Ortu | Nama kelas, wali kelas, jumlah siswa |
| 5 | Data Master → Kalender Akademik | Admin/Waka | `GET /api/v1/akademik/kalender` | GET | Siswa, Ortu | Tanggal libur, event sekolah |
| 6 | Kurikulum → CP/TP/ATP | Admin/Waka | `GET /api/v1/akademik/kurikulum/tp` | GET | Siswa, Ortu | Daftar TP per mapel (untuk label nilai) |
| 7 | Penjadwalan | Waka | `GET /api/v1/akademik/jadwal` | GET | Siswa | Jadwal harian/mingguan |
| 8 | Penjadwalan | Waka | `GET /api/v1/children/{id}/jadwal` | GET | Ortu | Jadwal anak |
| 9 | **Input Nilai per TP** | **Guru Mapel** | `GET /api/v1/penilaian/nilai` | GET | **Siswa** | **Nilai per TP, status Tuntas/Remedial** |
| 10 | Input Nilai per TP | Guru Mapel | `GET /api/v1/children/{id}/nilai` | GET | **Ortu** | Nilai anak + grafik tren |
| 11 | **Validasi Rapor** | **Wali Kelas** | `GET /api/v1/penilaian/rapor` | GET | **Siswa, Ortu** | **Rapor digital + link download PDF** |
| 12 | Catatan Wali Kelas | Wali Kelas | (termasuk di rapor) | GET | Siswa, Ortu | Deskripsi sikap, saran |
| 13 | **Asesmen P5** | **Fasilitator P5** | `GET /api/v1/penilaian/p5` | GET | **Siswa, Ortu** | **Progres, jurnal, 6 dimensi PPP** |
| 14 | Presensi Harian | Guru Piket/Mapel | `GET /api/v1/presensi/riwayat` | GET | Siswa, Ortu | Riwayat H/I/S/A, kalender |
| 15 | **Generate Tagihan** | **Bendahara** | `GET /api/v1/keuangan/tagihan` | GET | **Ortu** | **Daftar tagihan + status lunas/blm** |
| 16 | Verifikasi Pembayaran | Bendahara | `GET /api/v1/keuangan/transaksi` | GET | Ortu | Riwayat pembayaran |
| 17 | **Bank Soal & Paket Ujian** | **Guru Mapel** | `GET /api/v1/ujian/paket` | GET | **Siswa** | **Daftar ujian tersedia** |
| 18 | Bank Soal & Paket Ujian | Guru Mapel | `GET /api/v1/ujian/paket/{id}` | GET | Siswa | Detail ujian (durasi, jumlah soal) |
| 19 | Sesi Ujian Aktif | Sistem | `GET /api/v1/ujian/sesi/start` | POST | Siswa | Mulai ujian — tampilan CBT |
| 20 | Sesi Ujian Aktif | Sistem | `POST /api/v1/ujian/sesi/{id}/jawab` | POST | Siswa | Submit jawaban per soal |
| 21 | Sesi Ujian Aktif | Sistem | `POST /api/v1/ujian/sesi/{id}/submit` | POST | Siswa | Submit final ujian |
| 22 | **Hasil Ujian** | Sistem (auto-score) | `GET /api/v1/ujian/hasil/{id}` | GET | **Siswa** | **Skor & status ujian** |
| 23 | Pengumuman | Wali Kelas/Kepsek | `GET /api/v1/announcements` | GET | Siswa, Ortu | Daftar pengumuman |
| 24 | **Pembayaran Online** | **Ortu (via portal)** | `POST /api/v1/keuangan/pembayaran` | **POST** | **Ortu** | **Initiate bayar → dapat VA/QRIS** |
| 25 | Pembayaran Callback | Midtrans/Xendit | `POST /api/v1/keuangan/callback` | POST | Backend | Webhook → auto update status |

### 3.3 Alur Konkret: Satu Fitur Lengkap

#### Contoh: Ujian Online — Dari Backend ke Portal

```
┌─────────────────────────────────────────────────────────────────┐
│  ALUR LENGKAP: UJIAN ONLINE                                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  BACKEND (GURU)              API               PORTAL (SISWA)   │
│  ────────────────          ──────             ────────────────   │
│                                                                 │
│  1. Buat soal di           (disimpan         (belum ada)        │
│     Bank Soal               ke DB)                              │
│                                                                 │
│  2. Buat Paket Ujian       (disimpan         (belum ada)        │
│     - Pilih soal            ke DB)                              │
│     - Set jadwal                                                │
│     - Set durasi                                                │
│     - Set token                                                 │
│                                                                 │
│  3. (Tanggal ujian tiba)                      4. Siswa login    │
│                                               → GET /ujian/paket│
│                                               ← JSON daftar     │
│                                                  ujian aktif    │
│                                                                 │
│                                              5. Klik "Mulai"    │
│                                               → POST /sesi/start│
│                                               ← Token sesi +    │
│                                                  soal acak      │
│                                                                 │
│  6. Monitoring real-time                     7. Siswa menjawab  │
│     → GET /monitoring/                        → POST /jawab     │
│       sesi/{id}                               (auto-save tiap   │
│     ← WebSocket update                        30 detik)         │
│                                                                 │
│                                              8. Submit final    │
│                                               → POST /submit    │
│                                                                 │
│  9. (Auto-score PG)                          10. Lihat hasil    │
│                                                → GET /hasil/{id}│
│                                                ← Skor + status  │
│                                                                 │
│  11. Koreksi esai                                              │
│      → Update skor                                             │
│                                                                 │
│  12. Lihat rekap kelas                      13. (Hasil final    │
│      → Analisis butir                          tampil di        │
│        soal                                    portal)          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### Contoh: Pembayaran SPP — Dari Backend ke Portal

```
┌─────────────────────────────────────────────────────────────────┐
│  ALUR LENGKAP: PEMBAYARAN SPP                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  BACKEND (BENDAHARA)         API               PORTAL (ORTU)    │
│  ──────────────────        ──────             ────────────────   │
│                                                                 │
│  1. Generate tagihan       (disimpan          (notifikasi       │
│     masal SPP Juni          ke DB,             muncul di         │
│     → 245 siswa             queue notif)       dashboard ortu)   │
│                                                                 │
│                                              2. Ortu login      │
│                                               → GET /tagihan    │
│                                               ← JSON: 2 anak,   │
│                                                  tagihan SPP    │
│                                                  Rp 350.000 x2  │
│                                                  status: Blm    │
│                                                                 │
│                                              3. Pilih tagihan   │
│                                               → POST /pembayaran│
│                                               ← JSON: VA number │
│                                                  Bank BNI       │
│                                                                 │
│                                              4. Bayar via       │
│                                                 mobile banking  │
│                                                                 │
│  5. Webhook callback       ← POST /callback                     │
│     dari Midtrans          → verifikasi                         │
│     → update status        → simpan transaksi                   │
│     → kirim notif WA                                         │
│                                                                 │
│  6. Lihat transaksi                         7. Status berubah   │
│     masuk di panel                           → 🟢 LUNAS         │
│     bendahara                                → bisa download    │
│                                                 kwitansi PDF    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.4 Keamanan & Middleware

| Middleware | Fungsi |
|---|---|
| `auth` | User harus login (web routes) |
| `auth:sanctum` | Token valid (API routes) |
| `role:admin,guru,wali_kelas,...` | Gate per role |
| `school_context` | Inject tahun_ajaran & semester aktif |
| `scope:siswa` | API — hanya data siswa login sendiri |
| `scope:ortu` | API — hanya data anak kandung/wali |
| `throttle:api` | API rate limit (60/min) |
| `throttle:web` | Web rate limit (300/min) |
| `verified` | Email terverifikasi |

**Pola Otorisasi di Controller:**

```php
// Contoh: Guru hanya bisa input nilai di kelas yang diampunya
public function store(NilaiRequest $request) {
    $this->authorize('inputNilai', [Mapel::class, $request->rombel_id]);
    // Policy check: apakah guru ini mengajar mapel ini di rombel ini?
}

// Contoh: Ortu hanya bisa lihat data anaknya
public function show($childId) {
    $child = Student::findOrFail($childId);
    $this->authorize('viewChild', $child);
    // Policy check: apakah student ini anak dari ortu yang login?
}
```

### 3.5 Job Queue — Proses Async

| Job | Trigger | Queue |
|---|---|---|
| `GenerateRaporPdf` | Wali kelas validasi rapor | `rapor` |
| `GenerateTagihanMasal` | Bendahara generate tagihan | `keuangan` |
| `KirimNotifikasiTagihan` | Setelah generate tagihan | `notifications` |
| `AutoScoreUjian` | Siswa submit ujian PG | `ujian` |
| `ExportDapodik` | Admin request export | `export` |
| `BackupDatabase` | Scheduler harian | `maintenance` |
| `ImportSiswaExcel` | Admin upload Excel | `import` |

---

## Ringkasan: Backend Internal SIAKAD

| Role | Modul Utama | Jumlah Halaman Kunci |
|---|---|---|
| **Admin** | Data master, user, kurikulum, konfigurasi | ~15 halaman |
| **Guru Mapel** | Input nilai, bank soal, paket ujian, P5 | ~10 halaman |
| **Wali Kelas** | Rekap rombel, validasi rapor, presensi, sikap | ~8 halaman |
| **Bendahara** | Master biaya, generate tagihan, transaksi, laporan | ~8 halaman |
| **Kepala Sekolah** | Dashboard eksekutif, monitoring, semua laporan | ~6 halaman |

**Total: ~47 halaman panel internal + 25 endpoint API untuk portal depan.**

**Arsitektur**: Backend (Laravel) adalah **single source of truth** — semua data masuk melalui panel internal → tersimpan di PostgreSQL → disajikan via REST API (Sanctum) ke portal siswa & orang tua. Portal depan hanya READ + beberapa POST terbatas (ujian, pembayaran).
