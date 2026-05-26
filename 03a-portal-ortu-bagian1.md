# Portal Orang Tua/Wali SIAKAD — UI/UX (Bagian 1)
## Kurikulum Merdeka | SMP & SMK

---

## 1. Struktur Menu & Sub-Menu

```
SIDEBAR NAVIGATION
═══════════════════════════════════════════════

👨‍👩‍👧  CHILD SWITCHER
     ┌─────────────────────────┐
     │ 👦 Ahmad Fauzi  [AKTIF] │  ← anak yang sedang dipantau
     │    VII-A · SMPN 1       │
     └─────────────────────────┘
     ┌─────────────────────────┐
     │ 👧 Siti Rahma           │  ← klik untuk ganti
     │    X-TKJ · SMKN 2       │
     └─────────────────────────┘
     ➕ Tambah Anak

─────────────────────────────────────────────

🏠  Dashboard
👤  Detail Anak
📅  Jadwal
📊  Nilai & Rapor
🎯  Projek P5
🕐  Presensi
💰  Pembayaran
📢  Pengumuman
⭐  Ekstrakurikuler
🧑  Profil Saya
🚪  Keluar
```

**Konsep Kunci Multi-Anak:**
- Switcher di sidebar atas selalu terlihat → klik ganti anak = seluruh konten berubah
- Dashboard menampilkan **ringkasan semua anak** + detail anak aktif
- "Tambah Anak" → input NIS/NISN → verifikasi admin

---

## 2. Layout Global

```
┌──────────────────────────────────────────────────────────────────┐
│  TOP BAR:  Logo Sekolah       🔔 (3)        👤 Bpk. Budi ▼     │
├──────────────┬───────────────────────────────────────────────────┤
│   SIDEBAR    │              CONTENT AREA                        │
│   (260px)    │                                                   │
│              │  ┌── RINGKASAN SEMUA ANAK ─────────────────────┐ │
│  Switcher    │  │ [Ahmad: 83🟢|88%🟡|⚠️250rb] [Siti:79🟡|92%✅]│ │
│  ─────────   │  └──────────────────────────────────────────────┘ │
│  Menu Items  │                                                   │
│  ─────────   │  ┌── DETAIL ANAK AKTIF ────────────────────────┐ │
│  Info Wali   │  │  [Grid 6 Card: Nilai│Presensi│Tagihan│...]  │ │
│  Kelas +     │  └──────────────────────────────────────────────┘ │
│  Kontak      │                                                   │
└──────────────┴───────────────────────────────────────────────────┘
```

**Bedanya dengan Portal Siswa:** lebih bersih (no panel kanan), ada switcher anak, sidebar bawah ada kontak wali kelas, aksi utama = bayar + pantau.

---

## 3. Deskripsi Tiap Halaman

### 3.1. LOGIN

```
┌────────────────────────────────────────────┐
│        [Logo Sekolah]                      │
│     Portal Orang Tua / Wali                │
│                                            │
│  ┌──────────────────────────────────┐      │
│  │  Email / No. HP                  │      │
│  │  [___________________________]   │      │
│  │  Kata Sandi                  👁  │      │
│  │  [___________________________]   │      │
│  │  [✓] Ingat saya (30 hari)        │      │
│  │  [       MASUK        ]          │      │
│  │  Belum punya akun? Daftar        │      │
│  │  Lupa kata sandi?                │      │
│  └──────────────────────────────────┘      │
│  Akun diverifikasi oleh admin sekolah.     │
└────────────────────────────────────────────┘
```

---

### 3.2. DASHBOARD (Halaman Utama)

```
┌──────────────────────────────────────────────────────────────────┐
│  🏠 Dashboard / Selamat datang, Bpk. Budi Hartono               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌── SEMUA ANAK ──────────────────────────────────────────────┐ │
│  │ ┌───────────────────────┐ ┌───────────────────────┐        │ │
│  │ │ 👦 Ahmad Fauzi        │ │ 👧 Siti Rahma         │        │ │
│  │ │ VII-A · SMPN 1        │ │ X-TKJ · SMKN 2        │        │ │
│  │ │ 📊 83.4 🟢 │ 🕐 88% 🟡│ │ 📊 79.2 🟡 │ 🕐 92% 🟢│        │ │
│  │ │ 💰 ⚠️ 250rb│ 🎯 P5 65%│ │ 💰 ✅ LUNAS│🏭 PKL 80%│        │ │
│  │ │ [Lihat Detail →]      │ │ [Lihat Detail →]      │        │ │
│  │ └───────────────────────┘ └───────────────────────┘        │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌── AHMAD FAUZI (ANAK AKTIF) ────────────────────────────────┐ │
│  │                                                             │ │
│  │ ┌─ NILAI TERBARU ──┐ ┌─ PRESENSI MEI ──┐ ┌─ TAGIHAN ─────┐ │ │
│  │ │ MTK   ████░ 82   │ │ ✅16 🟡1 🟠1 🔴0│ │ ⚠️ SPP Mei    │ │ │
│  │ │ B.Ind █████░ 88  │ │                  │ │ Rp 250.000    │ │ │
│  │ │ IPA   ███░░░ 78  │ │ Kehadiran: 88%  │ │ Jatuh: 25 Mei │ │ │
│  │ │ Rata2   83.4 🟢  │ │ [Presensi →]    │ │ [Bayar →]     │ │ │
│  │ └──────────────────┘ └─────────────────┘ └───────────────┘ │ │
│  │                                                             │ │
│  │ ┌─ UJIAN TERDEKAT ─┐ ┌─ P5 AKTIF ──────┐ ┌─ PENGUMUMAN ──┐ │ │
│  │ │ ⏰ MTK-Sumatif   │ │ Kewirausahaan     │ │ 📌 Ambil rapor│ │ │
│  │ │ Selasa, 24 Mei   │ │ "Bazar Makanan"   │ │ 25 Juni 2026 │ │ │
│  │ │ 08:00 · 90 menit │ │ Progress: 65%     │ │ 📌 Libur 1 Jun│ │ │
│  │ └──────────────────┘ └──────────────────┘ └───────────────┘ │ │
│  └─────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

**6 Card per anak aktif:** Nilai (progress bar), Presensi (4 angka besar), Tagihan, Ujian, P5, Pengumuman.

### 3.3. DETAIL ANAK

```
┌──────────────────────────────────────────────────────────────────┐
│  👤 Detail Anak / Ahmad Fauzi                                    │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─ PROFIL ───────────────────────────────────────────────────┐ │
│  │ [Foto] Ahmad Fauzi  |  NIS: 20241001  |  NISN: 0091234567 │ │
│  │ Kelas: VII-A  |  Angkatan: 2024  |  Semester: Genap 2026  │ │
│  │ Sekolah: SMP Negeri 1 Bandung | 📍 Jl. Merdeka No. 10     │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌─ WALI KELAS ───────────────────────────────────────────────┐ │
│  │ 👩‍🏫 Ibu Ratnasari, S.Pd. — Wali Kelas VII-A                │ │
│  │ 📞 0852-xxxx-xxxx (WA)  │  📧 ratnasari@smpn1.sch.id       │ │
│  │ [💬 Chat WA]  [📧 Kirim Email]                             │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌─ [SMK] INFORMASI PKL ──────────────────────────────────────┐ │
│  │ DU/DI: PT Teknologi Nusantara | Periode: Jan-Apr 2026      │ │
│  │ Pembimbing: Bpk. Hendra · 0811-xxxx-xxxx                   │ │
│  └──────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

### 3.4. JADWAL

Sama dengan portal siswa — grid mingguan 6 kolom, warna intrakurikuler 🟦 / P5 🟩, klik untuk info guru & ruangan. Navigasi minggu dengan ◄ ►.

---

### 3.5. NILAI & RAPOR

```
┌──────────────────────────────────────────────────────────────────┐
│  📊 Nilai & Rapor / Ahmad Fauzi — Sem. Genap 2025/2026           │
├──────────────────────────────────────────────────────────────────┤
│  [Tab] RINGKASAN | RIWAYAT NILAI | RAPOR DIGITAL                │
│                                                                  │
│  ┌─ GRAFIK PERKEMBANGAN (3 semester) ─────────────────────────┐ │
│  │  100 ┤              ●──● B.Indo                           │ │
│  │   90 ┤  ●────●──────●                                      │ │
│  │   80 ┤  ●────●──────●──●──● MTK                           │ │
│  │   70 ┤       ●──────●      ●──● IPA                       │ │
│  │      └───┼──────┼──────┼──────┼──────                     │ │
│  │        Smt 1  Smt 2  Smt 3  Smt 4                         │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌─ NILAI PER MAPEL ──────────────────────────────────────────┐ │
│  │ Mapel          │ Nilai │ Status │ vs Smt Lalu             │ │
│  │ ───────────────┼───────┼────────┼───────────────────────── │ │
│  │ Matematika     │ 83.5  │ 🟢     │ 📈 +3.5 ▲               │ │
│  │ B.Indonesia    │ 88.0  │ 🟢     │ 📈 +2.0 ▲               │ │
│  │ B.Inggris      │ 79.0  │ 🟡 Rem │ 📉 -1.0 ▼ ⚠️           │ │
│  │ IPA            │ 73.5  │ 🟡 Rem │ 📉 -4.5 ▼ ⚠️           │ │
│  │ IPS            │ 86.5  │ 🟢     │ 📈 +1.5 ▲               │ │
│  │ PAI            │ 91.0  │ 🟢     │ 📈 +3.0 ▲               │ │
│  │ PJOK           │ 86.5  │ 🟢     │ 📊  0.0 =               │ │
│  └────────────────┴───────┴────────┴───────────────────────────┘ │
│  Rata-rata: 83.4 🟢 | Peringkat: 5/32                            │
│  ⚠️ Perlu perhatian: B.Inggris, IPA                              │
│                                                                  │
│  ┌─ RAPOR DIGITAL ────────────────────────────────────────────┐ │
│  │ 📄 Rapor Sem. Genap 2025/2026  ✅ Tervalidasi              │ │
│  │ [👁 Pratinjau] [📥 Download PDF] [🖨 Cetak]                │ │
│  │ 📄 Rapor P5 — Kewirausahaan    ✅ Tersedia                 │ │
│  │ 📄 Rapor P5 — Bangunlah Jiwa   ✅ Tersedia                 │ │
│  │ Riwayat: Sem. Ganjil 25 ↓ | Sem. Genap 25 ↓ | Sem. Ganjil 24 ↓│
│  └──────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

**Fitur kunci nilai:**
- Grafik tren **3-4 semester** (bukan hanya semester ini) → orang tua bisa lihat perkembangan
- **Δ (delta) vs semester lalu**: naik 📈 / turun 📉 / stabil 📊 — highlight merah jika turun signifikan
- Mapel bermasalah di-highlight ⚠️ agar orang tua langsung notice

---

### 3.6. PROJEK P5

```
┌──────────────────────────────────────────────────────────────────┐
│  🎯 Projek P5 / Ahmad Fauzi                                     │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─ AKTIF ────────────────────────────────────────────────────┐ │
│  │ Kewirausahaan: "Bazar Makanan Sehat Nusantara"             │ │
│  │ Kelompok 3 | Fasilitator: Bu Dewi                          │ │
│  │                                                            │ │
│  │ Timeline:                                                  │ │
│  │ ✅ Pengenalan (10-12 Apr)   ✅ Kontekstualisasi (13-17)   │ │
│  │ ✅ Riset (18-30 Apr)         🔄 Produksi (1-20 Mei) 65%   │ │
│  │ ⏳ Presentasi (23-27 Mei)    ⏳ Bazar & Evaluasi (30 Mei+) │ │
│  │ Progress: ████████████░░░░░░░░ 65%                        │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌─ PENILAIAN P5 ─────────────────────────────────────────────┐ │
│  │ Dimensi PPP                            │ Capaian           │ │
│  │ ───────────────────────────────────────┼───────────────────│ │
│  │ 1. Beriman & Bertakwa                 │ Mulai Berkembang  │ │
│  │ 2. Berkebinekaan Global               │ Berkembang        │ │
│  │ 3. Gotong Royong                      │ Sangat Berkembang │ │
│  │ 4. Mandiri                            │ Berkembang        │ │
│  │ 5. Bernalar Kritis                    │ Berkembang        │ │
│  │ 6. Kreatif                            │ Sangat Berkembang │ │
│  ├───────────────────────────────────────┴───────────────────│ │
│  │ Catatan: "Ahmad aktif dan kreatif. Inisiatif tinggi."     │ │
│  └──────────────────────────────────────────────────────────────┘ │
│  📌 Riwayat: ✅ Bangunlah Jiwa Raganya (Smt 1) — Selesai        │
└──────────────────────────────────────────────────────────────────┘
```
