# Backend & Panel Internal — Bagian 2: Guru Mapel & Wali Kelas

> Fokus: Panel untuk Guru Mapel (input nilai, bank soal, ujian online, P5) dan Wali Kelas (rekap, validasi rapor, presensi).

---

## Daftar Isi

- [1. Guru Mapel — Panel Pengajaran](#1-guru-mapel--panel-pengajaran)
- [2. Wali Kelas — Panel Manajemen Rombel](#2-wali-kelas--panel-manajemen-rombel)

---

## 1. Guru Mapel — Panel Pengajaran

### 1.1 Menu Guru Mapel

```
🏠 Dashboard Guru
├── Card: jadwal hari ini, ujian terdekat, status input nilai per kelas
├── Quick Actions: input nilai, tambah soal, buat paket ujian

📅 Jadwal Mengajar Saya
├── Tampilan mingguan — mapel, kelas, jam, ruangan
└── Filter: per hari / per minggu

📊 Penilaian
├── Input Nilai (pilih mapel → kelas → tabel siswa × TP)
├── Riwayat Input Nilai — log perubahan
└── Nilai Ekstrakurikuler (jika guru adalah pembina ekskul)

📝 Projek P5 (jika ditugaskan sebagai fasilitator)
├── Daftar Projek Saya
├── Input Jurnal Aktivitas Siswa
└── Asesmen 6 Dimensi PPP

📋 Ujian Online
├── Bank Soal → Tambah/Edit/Import Soal
├── Paket Ujian → Buat paket + atur jadwal
├── Monitoring Ujian → Sesi aktif (real-time)
└── Hasil Ujian → Rekap per kelas + analisis butir soal

👨‍🎓 Data Siswa (read-only)
└── Daftar siswa per kelas yang diampu

📄 Laporan
├── Laporan Nilai per Kelas
└── Cetak Daftar Nilai
```

### 1.2 Halaman Input Nilai (Kurikulum Merdeka)

**Alur**: Pilih Mapel → Pilih Kelas → Tabel Siswa × TP

```
┌──────────────────────────────────────────────────────────────────┐
│ Input Nilai: B.Indonesia — Kelas 7A           KKTP: 70           │
│ Semester Ganjil 2025/2026           [Simpan] [Ekspor Excel]      │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Mode tampilan: [Semua TP ▼]  Nilai sebagai: [Angka ▼]          │
│                                                                  │
│ ┌──────┬──────────────┬──────────┬──────────┬──────────┬──────┐  │
│ │ No   │ Nama         │ TP-01    │ TP-02    │ TP-03    │ Rata │  │
│ │      │              │ Teks LHO │ Struktur │ Evaluasi │ -rata│  │
│ ├──────┼──────────────┼──────────┼──────────┼──────────┼──────┤  │
│ │ 1    │ Adi Pratama  │ 85 🟢    │ 78 🟢    │   —      │ 81.5 │  │
│ │ 2    │ Budi Santoso │ 72 🟢    │ 60 🟡    │ 70 🟢    │ 67.3 │  │
│ │ 3    │ Citra Dewi   │ 90 🟢    │ 88 🟢    │ 92 🟢    │ 90.0 │  │
│ │ ...  │ ...          │          │          │          │      │  │
│ └──────┴──────────────┴──────────┴──────────┴──────────┴──────┘  │
│                                                                  │
│ 🟢 Tuntas (≥70)   🟡 Remedial (<70)   — Belum diisi             │
│                                                                  │
│ Klik cell → input angka 0-100 → auto-save (debounce 1 detik)    │
│                                                                  │
│ [💾 Simpan Semua]  [📋 Ekspor ke Excel]  [🖨 Cetak]              │
└──────────────────────────────────────────────────────────────────┘
```

**Fitur Input Nilai:**
- **Input per TP** — setiap TP dari Kurikulum Merdeka punya kolom tersendiri
- **Mode tampilan**: semua TP (scroll horizontal) atau satu TP per halaman
- **Nilai sebagai**: angka (0-100), predikat (A/B/C/D), atau deskripsi
- **Validasi otomatis**: nilai < KKTP → flag remedial 🟡; nilai di luar 0-100 → error
- **Auto-save**: setiap perubahan tersimpan otomatis (debounce 1 detik)
- **Rata-rata otomatis**: kolom paling kanan — rata-rata semua TP
- **Warna status**: hijau = tuntas, kuning = remedial, abu-abu = belum diisi
- **Filter per TP**: dropdown TP untuk fokus input satu TP saja
- **Shortcut keyboard**: Tab/Enter untuk pindah cell, panah untuk navigasi

### 1.3 Bank Soal

**Menu Bank Soal:**

```
📋 Bank Soal — B.Indonesia — Kelas 7
├── Filter: [TP ▼]  [Jenis Soal ▼]  [Cari...]
├── Tabel soal: No | Pertanyaan | TP | Jenis | Tingkat Kesulitan | Aksi
└── Tombol: [+ Tambah Soal] [📥 Import Excel]
```

**Form Tambah/Edit Soal:**

| Field | Keterangan |
|---|---|
| TP | Pilih TP terkait |
| Jenis Soal | Pilihan Ganda / Esai / Benar-Salah / Menjodohkan / Isian Singkat |
| Tingkat Kesulitan | LOTS / MOTS / HOTS (taksonomi Kurikulum Merdeka) |
| Pertanyaan | Rich text editor (bisa embed gambar, rumus matematika LaTeX) |
| Opsi Jawaban | Untuk PG: 5 opsi (A-E) + kunci jawaban |
| Pembahasan | Opsional — tampil setelah ujian selesai |
| Bobot Skor | Default 1, bisa disesuaikan |

**Import Soal via Excel:**

Template Excel:
```
| TP_Code | Jenis | Kesulitan | Pertanyaan | Opsi_A | Opsi_B | Opsi_C | Opsi_D | Opsi_E | Kunci | Bobot |
| TP-01   | PG    | MOTS      | Teks soal..| ...    | ...    | ...    | ...    | ...    | A     | 1     |
```

Validasi saat import: cek TP code valid, kunci jawaban valid, format soal sesuai.

### 1.4 Paket Ujian

**Buat Paket Ujian — Wizard 4 langkah:**

```
Step 1: Info Dasar
├── Nama Paket: "UH 1 — Teks Laporan Hasil Observasi"
├── Mapel: B.Indonesia
├── Kelas: [✓ 7A] [✓ 7B] [ 7C]  (multi-select)
└── Deskripsi (opsional)

Step 2: Pilih Soal
├── Mode: [Pilih Manual] atau [Acak dari Bank Soal]
├── Filter TP: [TP-01 ▼] → tampil soal-soal TP-01
├── Checklist soal yang dipilih (target: 20 soal)
└── Total terpilih: 15/20 soal

Step 3: Konfigurasi Ujian
├── Durasi: [60] menit
├── Jumlah soal ditampilkan: [20] (bisa diacak)
├── Acak soal: [✓] Ya  [ ] Tidak
├── Acak opsi: [✓] Ya  [ ] Tidak
├── Tampilkan hasil: [ ] Langsung  [✓] Setelah semua selesai
└── Buka kunci jawaban: [ ] Ya  [✓] Tidak (hanya skor)

Step 4: Jadwal
├── Tanggal & Jam Mulai: [15 Juni 2026] [08:00]
├── Tanggal & Jam Selesai: [15 Juni 2026] [09:00]
└── Token/Kode Ujian: auto-generate atau manual
```

### 1.5 Monitoring Ujian (Real-time)

```
┌──────────────────────────────────────────────────────────────┐
│ Monitoring: UH 1 — Teks LHO — 7A                  ⏱ 28:15   │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ ┌──────────┬──────────────┬────────┬──────────┬───────────┐  │
│ │ No       │ Nama         │ Status │ Progress │ Waktu     │  │
│ ├──────────┼──────────────┼────────┼──────────┼───────────┤  │
│ │ 1        │ Adi Pratama  │ 🟢 On  │ 15/20    │ 28:15     │  │
│ │ 2        │ Budi Santoso │ 🟢 On  │ 12/20    │ 27:50     │  │
│ │ 3        │ Citra Dewi   │ ✅ Done│ 20/20    │ 25:10     │  │
│ │ 4        │ Dina Putri   │ 🟡 Idle│ 5/20     │ 15:30     │  │
│ │ 5        │ Eko Saputra  │ 🔴 DC  │ 8/20     │ —         │  │
│ │ ...      │ ...          │        │          │           │  │
│ └──────────┴──────────────┴────────┴──────────┴───────────┘  │
│                                                              │
│ Summary: 30/35 siswa hadir | 3 Done | 25 On | 1 Idle | 1 DC │
│                                                              │
│ [👁 Lihat Jawaban Siswa]  [⏹ Tutup Sesi]  [📊 Rekap Hasil]   │
└──────────────────────────────────────────────────────────────┘
```

**Status real-time:**
- 🟢 On = sedang mengerjakan (aktif)
- ✅ Done = sudah submit
- 🟡 Idle = login tapi tidak ada aktivitas > 5 menit
- 🔴 DC = disconnect / keluar tanpa submit

Guru bisa **melihat jawaban** siswa yang sudah submit (untuk esai — perlu dikoreksi manual). Menggunakan **Laravel Reverb** (WebSocket) untuk update real-time.

### 1.6 Hasil Ujian & Analisis

**Rekap Hasil per Kelas:**

| No | Nama | Skor | Nilai (0-100) | Status | Waktu |
|---|---|---|---|---|---|
| 1 | Citra Dewi | 18/20 | 90 | Tuntas | 25 menit |
| 2 | Adi Pratama | 16/20 | 80 | Tuntas | 28 menit |
| ... | ... | ... | ... | ... | ... |
| | **Rata-rata** | | **72.5** | | |

**Analisis Butir Soal sederhana:**
- Tingkat kesukaran: % siswa menjawab benar per soal
- Soal terlalu mudah (≥90% benar) → flag tinjau ulang
- Soal terlalu sulit (≤30% benar) → flag tinjau ulang
- Daya beda kasar: bandingkan kelompok atas vs bawah

### 1.7 Input Projek P5 (jika sebagai fasilitator)

```
┌──────────────────────────────────────────────────────────────┐
│ Asesmen P5: "Ecobrick — Solusi Sampah Plastik"               │
│ Kelas 7A — Semester Genap 2025/2026                          │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ Tema: Gaya Hidup Berkelanjutan                               │
│                                                              │
│ Penilaian 6 Dimensi PPP (Profil Pelajar Pancasila):          │
│                                                              │
│ ┌──────┬──────────┬──────┬──────┬──────┬──────┬──────┬────┐  │
│ │ No   │ Nama     │ D1   │ D2   │ D3   │ D4   │ D5   │ D6 │  │
│ │      │          │ Iman │Kebin │Gotong│Mandiri│Kritis│Krea│  │
│ ├──────┼──────────┼──────┼──────┼──────┼──────┼──────┼────┤  │
│ │ 1    │ Adi      │ MB   │ BSH  │ BSH  │ SB   │ MB   │BSH │  │
│ │ 2    │ Budi     │ BSH  │ BSH  │ MB   │ BSH  │ MB   │BSH │  │
│ └──────┴──────────┴──────┴──────┴──────┴──────┴──────┴────┘  │
│                                                              │
│ Keterangan: BB=Baru Berkembang, MB=Cukup,                    │
│             BSH=Berkembang Sesuai Harapan, SB=Sangat         │
│                                                              │
│ Mode input: [Dropdown per cell]  atau  [Form per siswa]      │
│                                                              │
│ [💾 Simpan]  [📋 Rekap Deskripsi Otomatis]                    │
└──────────────────────────────────────────────────────────────┘
```

6 Dimensi PPP:
1. **Beriman & Bertakwa** kepada Tuhan YME & Berakhlak Mulia
2. **Berkebinekaan Global**
3. **Gotong Royong**
4. **Mandiri**
5. **Bernalar Kritis**
6. **Kreatif**

---

## 2. Wali Kelas — Panel Manajemen Rombel

### 2.1 Menu Wali Kelas

```
🏠 Dashboard Wali Kelas
├── Card: jumlah siswa rombel, presensi hari ini, status input nilai semua mapel
├── Peringatan: siswa dengan nilai < KKTP di >3 mapel, siswa sering alfa
└── Quick Actions: lihat rapor, input catatan wali kelas

👨‍🎓 Siswa Rombel Saya
├── Daftar siswa (foto, nama, NIS, alamat, kontak ortu)
├── Profil detail per siswa
└── Kontak orang tua/wali

📊 Rekap Akademik
├── Rekap Nilai Semua Mapel — tabel ringkasan siswa × mapel
├── Ranking — (opsional, Kurikulum Merdeka tidak wajib)
└── Status Tuntas/Remedial per siswa

📝 Projek P5
├── Progres P5 rombel — lihat semua projek
├── Jurnal Aktivitas — per projek
└── Asesmen P5 — rekap 6 dimensi

✅ Presensi
├── Rekap Harian — siapa hadir/izin/sakit/alfa hari ini
├── Rekap Bulanan — persentase per siswa
├── Kalender Presensi — tampilan bulanan kode warna
└── Notifikasi — kirim peringatan ke ortu (Alfa >3x)

📋 Rapor
├── Lihat Rapor Sementara — preview sebelum divalidasi
├── Validasi Rapor — checklist per siswa → [Validasi Semua]
├── Input Catatan Wali Kelas — deskripsi sikap, saran
├── Cetak Rapor — generate PDF per siswa atau masal
└── Status Validasi — siapa sudah/belum divalidasi

📊 Sikap
├── Observasi Sikap — catatan harian (spiritual & sosial)
└── Rekap Sikap — untuk rapor

📢 Komunikasi
├── Pengumuman — kirim pengumuman ke orang tua rombel
└── Kontak Darurat — daftar kontak ortu/wali
```

### 2.2 Dashboard Wali Kelas

```
┌──────────────────────────────────────────────────────────────┐
│ Dashboard — Wali Kelas 7A                   2025/2026 Genap  │
│ Wali Kelas: Ibu Ani Susanti, S.Pd.       35 siswa            │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐  │
│ │ ⚠️ 3 siswa │ │ 📊 5 siswa │ │ 📋 2 mapel │ │ 🔔 7 ortu  │  │
│ │ Alfa >3x  │ │ Remedial   │ │ blm input  │ │ blm baca   │  │
│ │ bulan ini │ │ >2 mapel   │ │ nilai      │ │ pengumuman │  │
│ └────────────┘ └────────────┘ └────────────┘ └────────────┘  │
│                                                              │
│ ┌──────────────────────────────────────────────────────┐     │
│ │ Status Input Nilai Mapel                             │     │
│ │ B.Indonesia ✅ │ Matematika ✅ │ IPA ⚠️ │ IPS ✅     │     │
│ │ B.Inggris ✅  │ PJOK ✅       │ Seni ⚠️ │ Info ✅  │     │
│ └──────────────────────────────────────────────────────┘     │
│                                                              │
│ ┌──────────────────────────┐ ┌──────────────────────────┐    │
│ │ ⚠️ Peringatan Remedial   │ │ 🔔 Presensi Terkini       │    │
│ │ Budi: 4 mapel < KKTP    │ │ Hari ini: 33 hadir, 1 s, │    │
│ │ Eko: 3 mapel < KKTP     │ │           1 i, 0 a       │    │
│ │ Fani: 3 mapel < KKTP    │ │ Kemarin: 32 hadir, 0 s,  │    │
│ │                          │ │          1 i, 2 a        │    │
│ └──────────────────────────┘ └──────────────────────────┘    │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 2.3 Validasi Rapor

**Alur Validasi Rapor oleh Wali Kelas:**

```
Step 1: Pastikan semua guru mapel sudah input nilai
        → Dashboard menunjukkan status input per mapel (✅/⚠️)

Step 2: Buka halaman "Rapor Sementara"
        → Tabel: siswa × ringkasan nilai semua mapel + presensi + P5

Step 3: Input Catatan Wali Kelas per siswa
        → Deskripsi sikap spiritual & sosial
        → Saran untuk semester berikutnya

Step 4: Checklist validasi per siswa
        → [✓] Adi Pratama — Nilai lengkap, siap validasi
        → [✓] Budi Santoso — Nilai lengkap, siap validasi
        → [ ] Citra Dewi — Menunggu nilai mapel Seni ⚠️

Step 5: [Validasi yang Diceklis] → Konfirmasi dialog
        → "4 rapor akan divalidasi. Setelah validasi, rapor terkunci."
        → [Ya, Validasi]

Step 6: Setelah validasi:
        → Rapor terkunci (tidak bisa diedit guru)
        → Job queue: generate PDF rapor per siswa
        → Status: "Tervalidasi" — muncul di portal siswa & ortu
        → Opsi: unduh PDF, cetak masal
```

**Tampilan Validasi Rapor:**

```
┌──────────────────────────────────────────────────────────────┐
│ Validasi Rapor — Kelas 7A — Semester Genap 2025/2026         │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ ┌──────┬──────────┬──────────┬────────┬────────┬──────────┐  │
│ │ ✓    │ Nama     │ Nilai    │ Presensi│ P5    │ Catatan  │  │
│ │      │          │ Lengkap? │        │       │ Wali     │  │
│ ├──────┼──────────┼──────────┼────────┼────────┼──────────┤  │
│ │ [✓] │ Adi      │ ✅ 12/12 │ 92%    │ ✅    │ ✓ Sudah  │  │
│ │ [✓] │ Budi     │ ✅ 12/12 │ 85%    │ ✅    │ ✓ Sudah  │  │
│ │ [ ] │ Citra    │ ⚠️ 11/12 │ 95%    │ ✅    │ — Blm    │  │
│ │ [✓] │ Dina     │ ✅ 12/12 │ 89%    │ ✅    │ ✓ Sudah  │  │
│ └──────┴──────────┴──────────┴────────┴────────┴──────────┘  │
│                                                              │
│ Progress: 15/35 siswa siap validasi                          │
│                                                              │
│ [✅ Validasi 3 Terpilih]  [📄 Preview Rapor]  [📋 Rekap]     │
└──────────────────────────────────────────────────────────────┘
```

### 2.4 Halaman Presensi Wali Kelas

```
┌──────────────────────────────────────────────────────────────┐
│ Presensi — Kelas 7A — Mei 2026              [◀ Bulan Lalu] [Bulan Depan ▶] │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│   Sen     Sel     Rab     Kam     Jum     Sab                │
│   1       2       3       4       5       6                  │
│   ✅33    ✅34    ✅33    ✅32    ✅33    —                   │
│   s1,i1   i1      s2      s1,i2   s1,i1                     │
│                                                              │
│   8       9       10      11      12      13                 │
│   ✅33    ✅35    ✅34    ✅32    🔴30    —                   │
│   s1,a1   —       i1      a2,s1   a3,a2                     │
│   ...                                                         │
│                                                              │
│ ✅ Hadir | 🟡 Sakit | 🟠 Izin | 🔴 Alfa/Tanpa Keterangan    │
│                                                              │
│ ┌──────────────────────────────────────────────────────┐     │
│ │ ⚠️ Peringatan: Eko Saputra — Alfa 4x bulan ini       │     │
│ │ [📩 Kirim Notifikasi ke Orang Tua]                    │     │
│ └──────────────────────────────────────────────────────┘     │
│                                                              │
│ [📊 Rekap Bulanan] [📥 Ekspor Excel] [📩 Kirim Laporan]      │
└──────────────────────────────────────────────────────────────┘
```

Wali kelas tidak menginput presensi (guru mapel/piket yang input), tapi wali kelas:
- Melihat rekap dan kalender presensi
- Mendeteksi siswa bermasalah (Alfa >3x)
- Mengirim notifikasi ke orang tua
- Memberikan catatan presensi untuk rapor

---

## Ringkasan: Siapa Input Apa → Siapa Konsumsi

| Data | Diinput oleh | Divalidasi oleh | Dikonsumsi Portal |
|---|---|---|---|
| Nilai per TP | Guru Mapel | Wali Kelas (rapor) | Siswa & Ortu |
| Soal & Paket Ujian | Guru Mapel | — | Siswa (ujian online) |
| Presensi harian | Guru Piket / Mapel | Wali Kelas (rekap) | Siswa & Ortu |
| Asesmen P5 | Fasilitator P5 | Koordinator P5 | Siswa & Ortu |
| Catatan Wali Kelas | Wali Kelas | — | Siswa & Ortu (rapor) |
| Rapor final | Sistem (generate) | Wali Kelas + Kepsek | Siswa & Ortu (download) |
