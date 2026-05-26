# Portal Siswa SIAKAD — Rancangan UI/UX (Bagian 1)
## Kurikulum Merdeka | SMP (Fase D VII-IX) & SMK

---

## 1. Struktur Menu Utama & Sub-Menu

```
SIDEBAR NAVIGATION
═══════════════════════════════════════════════

🏠  Dashboard
📅  Jadwal
     ├── Jadwal Pelajaran
     └── Jadwal Ujian
📝  Ujian Online
     ├── Ujian Aktif
     ├── Riwayat Ujian
     └── [Sedang Mengerjakan]  ← muncul hanya saat ujian
📊  Nilai & Rapor
     ├── Nilai Intrakurikuler
     ├── Nilai Projek P5
     └── Rapor (Download)
🎯  Projek P5
     ├── Projek Saya
     ├── Timeline & Aktivitas
     └── Penilaian P5
🕐  Presensi
     ├── Kehadiran Hari Ini
     └── Riwayat Presensi
💰  Pembayaran
     ├── Tagihan Saya
     ├── Riwayat Pembayaran
     └── Bayar Sekarang
📢  Pengumuman
⭐  Ekstrakurikuler
🧑  Profil
🏭  [SMK ONLY] PKL / Prakerin
🚪  Keluar
```

---

## 2. Layout Global: Three-Column Panel

```
┌──────────────┬─────────────────────────────────┬──────────────┐
│   SIDEBAR    │         CONTENT AREA            │ PANEL KANAN  │
│   (240px)    │         (tengah)                │ (280px)      │
│              │                                 │              │
│  Logo Sek.  │  Breadcrumb + Judul Halaman     │  Notifikasi  │
│  ─────────  │  ────────────────────────        │  Ujian Dekat │
│  Menu Item  │                                 │  Tagihan     │
│  .          │   MAIN CONTENT                  │  Quick Links │
│  .          │   (Cards / Table / Form)        │              │
│  .          │                                 │              │
│  ─────────  │                                 │              │
│  Avatar     │                                 │              │
│  Nama+Kelas │                                 │              │
└──────────────┴─────────────────────────────────┴──────────────┘
│                    TOP BAR (sticky)                            │
│  ☰ (mobile)     🔔 Notif     👤 Profile                       │
└───────────────────────────────────────────────────────────────┘
```

**Responsivitas:**
| ≥1280px Desktop | 768-1279px Tablet | <768px Mobile |
|---|---|---|
| Sidebar terbuka 240px + Panel kanan 280px | Sidebar collapse ikon 64px, panel disembunyikan | Drawer dari kiri, panel hilang |

**Warna tematik per jenjang:**
- SMP: 🟦 Biru (#2563EB)
- SMK: 🟧 Oranye (#EA580C)
- Status: 🟢 Tuntas/Hadir, 🟡 Peringatan, 🔴 Terlambat/Jatuh Tempo

---

## 3. Deskripsi Detail Tiap Halaman

---

### 3.1. HALAMAN LOGIN

```
┌────────────────────────────────────────────┐
│          [Logo Sekolah]                    │
│       SIAKAD SMP/SMK NEGERI ...            │
│                                            │
│   ┌──────────────────────────────────┐     │
│   │  NIS / NISN                      │     │
│   │  [___________________________]   │     │
│   │  Kata Sandi                  👁  │     │
│   │  [___________________________]   │     │
│   │  [✓] Ingat saya (30 hari)        │     │
│   │  [       MASUK        ]          │     │
│   │  Lupa kata sandi?                │     │
│   └──────────────────────────────────┘     │
│  Siswa hanya bisa login via portal ini.    │
│  Guru & Admin → panel backend terpisah.    │
└────────────────────────────────────────────┘
```

---

### 3.2. DASHBOARD SISWA

**Tujuan:** "Apa yang perlu saya tahu dan lakukan hari ini?" — satu layar ringkas.

```
┌──────────────────────────────────────────────────────────────────┐
│  Dashboard / Halo, Ahmad Fauzi!      VII-A  │  Smt Genap 2025/26 │
├───────────────────────────────┬──────────────────────────────────┤
│  📅 JADWAL HARI INI (Senin)   │  🔔 NOTIFIKASI (3 baru)         │
│  ┌─────────────────────────┐  │  📌 Ujian MTK besok jam 08:00  │
│  │ 07:15  Matematika       │  │  📌 Tugas P5 deadline Jumat    │
│  │ 08:35  B.Indonesia       │  │  ⚠️  Tagihan SPP Mei jatuh     │
│  │ 09:55  Istirahat         │  │     tempo 3 hari lagi         │
│  │ 10:15  IPA               │  ├──────────────────────────────────┤
│  │ 11:35  P5 - Projek       │  │  📝 UJIAN TERDEKAT             │
│  └─────────────────────────┘  │  ┌────────────────────────────┐  │
│                               │  │ ⏰ MTK - Sumatif Akhir      │  │
│  💰 PEMBAYARAN                │  │ Selasa, 24 Mei, 08:00      │  │
│  ┌─────────────────────────┐  │  │ 90 menit | 40 soal         │  │
│  │ SPP Mei: Rp 250.000     │  │  │ [Lihat Detail]             │  │
│  │ ⚠️ Jatuh tempo 25 Mei   │  │  └────────────────────────────┘  │
│  │ [Bayar Sekarang]         │  │                                  │
│  └─────────────────────────┘  │  📊 RINGKASAN NILAI             │
│                               │  ┌────────────────────────────┐  │
│  🎯 P5 AKTIF                  │  │ MTK ████████░░ 82          │  │
│  ┌─────────────────────────┐  │  │ B.Ind █████████░ 88       │  │
│  │ Kewirausahaan: "Bazar   │  │  │ IPA  ███████░░░ 78        │  │
│  │ Makanan Sehat"          │  │  │ Rata: 83.4 🟢             │  │
│  │ Progress ██████░░ 65%   │  │  └────────────────────────────┘  │
│  └─────────────────────────┘  │                                  │
│                               │  🕐 PRESENSI HARI INI            │
│                               │  ✓ Hadir | 07:10 | Tepat Waktu   │
├───────────────────────────────┴──────────────────────────────────┤
│  [Quick] 📝 Mulai Ujian  │ 💰 Bayar SPP  │ 📊 Lihat Rapor       │
└──────────────────────────────────────────────────────────────────┘
```

**6 Card Utama Dashboard:**
| Card | Data yang Ditampilkan |
|---|---|
| Jadwal Hari Ini | 5-8 slot, highlight jam sekarang, warna beda intrakurikuler vs P5 |
| Status Pembayaran | Tagihan aktif, status, jatuh tempo, CTA "Bayar Sekarang" |
| Ujian Terdekat | 1-3 ujian mendatang + countdown realtime |
| Projek P5 | Progress bar, tema, next deadline |
| Ringkasan Nilai | Progress bar per mapel + rata-rata semester |
| Presensi Hari Ini | Status + jam check-in |

**Perilaku:** auto-scroll jadwal ke jam aktif, countdown timer, badge merah notifikasi, semua card clickable.

---

### 3.3. JADWAL PELAJARAN

```
┌──────────────────────────────────────────────────────────────────┐
│  📅 Jadwal Pelajaran / VII-A — Semester Genap                   │
├──────────────────────────────────────────────────────────────────┤
│  [◄ 23-28 Mei 2026 ►]   [Hari Ini]  [Mingguan]                  │
│                                                                  │
│  ┌──────────┬──────────┬──────────┬──────────┬──────────┐       │
│  │ Senin    │ Selasa   │ Rabu     │ Kamis    │ Jumat    │       │
│  ├──────────┼──────────┼──────────┼──────────┼──────────┤       │
│  │🟦 MTK    │🟦 B.Indo │🟦 IPA    │🟦 IPS    │🟦 PAI    │       │
│  │07:15-0835│07:15-0835│07:15-0835│07:15-0835│07:15-0800│       │
│  ├──────────┼──────────┼──────────┼──────────┼──────────┤       │
│  │🟦 B.Indo │🟦 MTK    │🟦 B.Ing  │🟦 Prakarya│🟦 PJOK  │       │
│  │08:35-0955│08:35-0955│08:35-0955│08:35-0955│08:00-0920│       │
│  ├──────────┼──────────┼──────────┼──────────┼──────────┤       │
│  │🟨 Isth   │🟨 Isth   │🟨 Isth   │🟨 Isth   │🟨 Isth   │       │
│  ├──────────┼──────────┼──────────┼──────────┼──────────┤       │
│  │🟦 IPA    │🟦 IPS    │🟦 MTK    │🟦 B.Ing  │🟦 B.Indo │       │
│  │10:15-1135│10:15-1135│10:15-1135│10:15-1135│09:20-1040│       │
│  ├──────────┼──────────┼──────────┼──────────┼──────────┤       │
│  │🟩 P5     │🟦 Informat│🟦 Seni   │          │          │       │
│  │11:35-1255│11:35-1255│11:35-1220│          │          │       │
│  └──────────┴──────────┴──────────┴──────────┴──────────┘       │
│                                                                  │
│  🟦 Intrakurikuler  🟩 P5  🟨 Istirahat                         │
│  Klik mapel → popup: guru, ruangan, CP yang sedang dipelajari    │
└──────────────────────────────────────────────────────────────────┘
```

**Fitur:**
- Toggle **Hari Ini** (list vertikal) vs **Mingguan** (grid)
- Highlight realtime jam yang sedang berlangsung
- Badge jika ada perubahan jadwal
- Info guru pengajar & ruangan via klik

---

### 3.4. UJIAN ONLINE — List Ujian

```
┌──────────────────────────────────────────────────────────────────┐
│  💻 Ujian Online                                                 │
├──────────────────────────────────────────────────────────────────┤
│  [Tab] 🟢 Tersedia (1)  │  ⏳ Terjadwal (2)  │  ✅ Selesai (5)  │
│                                                                  │
│  ┌─ UJIAN TERSEDIA ──────────────────────────────────────────┐  │
│  │  📝 Matematika — Sumatif Akhir Semester                   │  │
│  │  ─────────────────────────────────────────────────────    │  │
│  │  📋 40 Soal (35 PG + 5 Esai)                             │  │
│  │  ⏱️ 90 Menit                                             │  │
│  │  📅 24 Mei 2026, 08:00 – 23:59 WIB                       │  │
│  │                                                           │  │
│  │  📌 Aturan:                                               │  │
│  │  • Tidak boleh buka tab lain                             │  │
│  │  • Soal diacak per siswa                                 │  │
│  │  • Jawaban tersimpan otomatis                             │  │
│  │  • Timer tidak bisa dijeda setelah mulai                  │  │
│  │                                                           │  │
│  │  [⚠️ MULAI UJIAN]  ← dialog konfirmasi dulu              │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ⏳ TERJADWAL                                                    │
│  • B.Indonesia — SAS  │ 25 Mei, 08:00  │ [Detail]               │
│  • IPA — SAS           │ 26 Mei, 08:00  │ [Detail]               │
│                                                                  │
│  ✅ SELESAI                                                      │
│  • B.Inggris — Formatif 3  │ ⭐ 85  │ 20 Mei  │ [Hasil]         │
│  • Informatika — Formatif 3 │ ⭐ 92  │ 18 Mei  │ [Hasil]         │
│  [Lihat Semua Riwayat →]                                        │
└──────────────────────────────────────────────────────────────────┘
```

---

### 3.5. UJIAN ONLINE — Tampilan Mengerjakan (Full-Screen)

```
┌──────────────────────────────────────────────────────────────────┐
│ │⚠️ Ujian Berlangsung — Jangan tutup halaman!          │        │
│ │📝 Matematika — Sumatif Akhir Semester                │        │
│ │⏱️ Sisa Waktu: 01:12:34                               │        │
├────────────────────────────┬─────────────────────────────────────┤
│  PANEL SOAL (70%)          │  NAVIGASI SOAL (30%)                │
│                            │                                     │
│  Soal No. 12 dari 40      │  ┌── Nomor Soal ───────────────┐   │
│  ┌──────────────────────┐  │  │ 1✓ 2✓ 3✓ 4✓ 5✓ 6  7  8  9 │   │
│  │ Sebuah persegi       │  │  │ 10 11 12 13 14 15 16 17 18 │   │
│  │ panjang memiliki     │  │  │ ...                        │   │
│  │ panjang (2x+3) cm    │  │  │ 36 37 38 39 40            │   │
│  │ dan lebar (x-1) cm.   │  │  └─────────────────────────────┘   │
│  │ Jika keliling = 46   │  │                                     │
│  │ cm, maka x = ...     │  │  📊 Status:                         │
│  │                      │  │  ✓ Dijawab: 11  ❓ Belum: 28        │
│  │ ○ A. 5               │  │  🔖 Ragu: 1                         │
│  │ ○ B. 6               │  │  🟢 PG (1-35)  🔵 Esai (36-40)    │
│  │ ● C. 7  ← dipilih    │  │                                     │
│  │ ○ D. 8               │  │  [☑ Tampilkan yg ragu]             │
│  │                      │  │  [☑ Tampilkan yg belum]            │
│  │ [🔖 Tandai Ragu]     │  │                                     │
│  │                      │  │                                     │
│  │ [◀ Sebelumnya]       │  │                                     │
│  │ [Selanjutnya ▶]      │  │                                     │
│  └──────────────────────┘  │                                     │
├────────────────────────────┴─────────────────────────────────────┤
│ ⚠️ Keluar full-screen 1x (maks 3x). Ujian terkumpul otomatis.   │
│                                   [Simpan]    [SELESAI UJIAN]    │
└──────────────────────────────────────────────────────────────────┘
```

**Fitur Kritis Ujian Online:**
1. **Full-screen wajib** — keluar 3x = auto-submit
2. **Soal & opsi diacak** per siswa
3. **Timer server-side** — tidak bisa manipulasi client
4. **Auto-save** tiap 30 detik & tiap ganti soal
5. **Tandai ragu** — untuk soal yang ingin dicek ulang
6. **Nomor soal** — kode warna: hijau=sudah, putih=belum, kuning=ragu
7. **Warning submit:** "Anda belum menjawab 5 soal. Yakin selesai?"
8. **Esai:** textarea dengan formatting dasar
9. **Koneksi putus:** jawaban tersimpan lokal, sync saat online kembali

---

### 3.6. UJIAN ONLINE — Hasil Ujian

```
┌──────────────────────────────────────────────────────────────────┐
│  📊 Hasil Ujian / Matematika — Sumatif Akhir Semester            │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐   ┌─────────────────────────────────────┐     │
│  │ ⭐ NILAI: 85 │   │ Benar (PG): 33/35 (94%)             │     │
│  │   ------     │   │ Esai: 82/100                         │     │
│  │    100       │   │ Tidak dijawab: 2                     │     │
│  │ 🟢 BAIK      │   │ Waktu: 72 menit | 24 Mei 2026       │     │
│  └──────────────┘   └─────────────────────────────────────┘     │
│                                                                  │
│  ┌─ DETAIL PER SOAL ─────────────────────────────────────────┐  │
│  │ No │ Tipe │ Jawaban │ Kunci │ Hasil    │ Bobot            │  │
│  │  1 │ PG   │  B      │  B    │ ✅ Benar │ 2.5             │  │
│  │  2 │ PG   │  D      │  C    │ ❌ Salah │ 2.5             │  │
│  │ ...│ ...  │  ...    │  ...  │  ...     │ ...             │  │
│  │ 36 │ Esai │ "x=7.." │(kunci)│ ⭐ 78    │ 5.0             │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  [Lihat Pembahasan] (jika diizinkan guru)                        │
└──────────────────────────────────────────────────────────────────┘
```

---

Lanjut ke Bagian 2: Nilai & Rapor, P5, Presensi, Pembayaran, Alur Utama.
