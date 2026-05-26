# Modul Exam/Ujian Online — Bagian 1: Fitur Guru/Backend

> Bank soal, paket ujian, keamanan, koreksi otomatis & manual, dan monitoring ujian.

---

## 1. Gambaran Umum & Koneksi Kurikulum Merdeka

### Jenis Ujian

| Jenis | Kurikulum Merdeka | Contoh |
|---|---|---|
| **Formatif** | Asesmen per TP/ATP | Ulangan Harian 1 — TP Teks Laporan |
| **Sumatif Lingkup Materi** | STS (Sumatif Tengah Semester) | STS Ganjil — B.Indonesia Kelas 7 |
| **Sumatif Akhir** | SAS / ASAJ (Akhir Semester/Jenjang) | SAS Genap, ASAJ Kelas 9 |
| **SMK Khusus** | UKK (Uji Kompetensi Kejuruan) | UKK TKJ — MikroTik |

### Koneksi ke Nilai & Rapor

```
Question.tp_id ────► TP (Tujuan Pembelajaran)
ExamResult.tp_scores ──► Nilai per TP ──► Modul Penilaian & Rapor
```

Setiap soal **di-tagging ke TP**, sehingga hasil ujian otomatis terurai menjadi **nilai per TP** dan bisa langsung masuk ke modul rapor.

---

## 2. Bank Soal

### 2.1 Tipe Soal & Metode Koreksi

| Tipe | Kode | Koreksi | Media |
|---|---|---|---|
| Pilihan Ganda | `pg` | Otomatis (exact) | Teks + gambar di stem & opsi |
| Pilihan Ganda Kompleks | `pgk` | Otomatis (exact) | Beberapa jawaban benar |
| Benar-Salah | `bs` | Otomatis (exact) | Pernyataan → B/S |
| Menjodohkan | `jodoh` | Otomatis (exact) | Pasangan kiri-kanan |
| Isian Singkat | `isian` | Semi-otomatis (fuzzy) | Teks pendek, toleransi typo |
| Esai | `esai` | **Manual** | Jawaban panjang |
| Audio | `audio` | Manual | Mendengarkan → menjawab |

### 2.2 Klasifikasi Soal

| Atribut | Nilai | Keterangan |
|---|---|---|
| **TP Tagging** | TP-01, TP-02, ... | Wajib — koneksi ke Kurikulum |
| **Level Kognitif** | L1 (LOTS), L2 (MOTS), L3 (HOTS) | Taksonomi Kurikulum Merdeka |
| **Kesulitan** | Mudah / Sedang / Sulit | Untuk analisis butir soal |
| **Bobot Skor** | 1–10 (default: 1) | Bisa berbeda per soal |

### 2.3 Form Tambah/Edit Soal (PG)

```
┌──────────────────────────────────────────────────────────┐
│  Tambah Soal — Pilihan Ganda                             │
├──────────────────────────────────────────────────────────┤
│  Mapel: [B.Indo ▼]  TP: [TP-01 ▼]  Level: [L2/MOTS ▼]  │
│  Kesulitan: [Sedang ▼]   Bobot: [2]                      │
│                                                          │
│  Pertanyaan:                                             │
│  ┌──────────────────────────────────────────────────┐   │
│  │ [B] [I] [U] [📷] [🎵] [Σ LaTeX]   Rich Editor   │   │
│  │                                                  │   │
│  │ [📷 teks_lho_paragraf2.png]                      │   │
│  │                                                  │   │
│  │ Struktur teks pada paragraf kedua adalah...      │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  Opsi:                                                   │
│  A [Definisi umum________________________]               │
│  B [Deskripsi bagian_____________________] 🔑 Kunci      │
│  C [Deskripsi manfaat____________________]               │
│  D [Simpulan_____________________________]               │
│  E [Argumentasi__________________________]               │
│                                                          │
│  Pembahasan (opsional):                                  │
│  [Paragraf kedua menjelaskan ciri-ciri fisik objek...]   │
│                                                          │
│  [💾 Simpan]  [💾 Simpan & Tambah Baru]                  │
└──────────────────────────────────────────────────────────┘
```

### 2.4 Manajemen Bank Soal

```
┌─────────────────────────────────────────────────────────────┐
│  Bank Soal — B.Indonesia    Filter: [TP ▼] [Tipe ▼] [Cari] │
├─────────────────────────────────────────────────────────────┤
│ ┌────┬──────────────────────────────┬──────┬──────┬──────┐ │
│ │ No │ Pertanyaan                   │ TP   │ Tipe │ Aksi │ │
│ ├────┼──────────────────────────────┼──────┼──────┼──────┤ │
│ │ 1  │ Bacalah teks berikut...      │ TP01 │ PG   │ ✏️🗑️│ │
│ │ 2  │ Apa struktur teks...         │ TP01 │ PG   │ ✏️🗑️│ │
│ │ 3  │ Jelaskan perbedaan fakta...  │ TP02 │ Esai │ ✏️🗑️│ │
│ │ 4  │ [🎵] Dengarkan rekaman...    │ TP03 │Audio │ ✏️🗑️│ │
│ └────┴──────────────────────────────┴──────┴──────┴──────┘ │
│                                                            │
│  Total: 124 soal | PG: 78 | Esai: 23 | BS: 12 | Lain: 11  │
│  [+ Tambah] [📥 Import Excel] [📤 Export] [🗑️ Hapus]     │
└─────────────────────────────────────────────────────────────┘
```

### 2.5 Import Soal via Excel

Template:

| Tipe | TP_Code | Level | Kesulitan | Pertanyaan | Opsi_A | ... | Opsi_E | Kunci | Bobot |
|---|---|---|---|---|---|---|---|---|---|
| PG | TP-01 | L2 | Sedang | Teks soal... | Def umum | ... | Argum | B | 2 |

Validasi: TP_Code harus valid, kunci sesuai format tipe soal. Baris error ditandai, yang valid tetap diimport.

---

## 3. Paket Ujian — Wizard 4 Langkah

### Step 1: Informasi Dasar
- Nama ujian, jenis (formatif/sumatif/STS/SAS), mapel
- Pilih kelas (multi-select: 7A, 7B, 7C)
- Token/kode ujian (auto-generate atau manual)

### Step 2: Pilih Soal
- **Mode Manual**: checklist soal dari bank soal, filter per TP
- **Mode Acak**: tentukan jumlah soal + komposisi per TP → sistem acak dari bank soal
- Informasi: total terpilih, komposisi TP, komposisi level kognitif

### Step 3: Konfigurasi Ujian

| Konfigurasi | Opsi |
|---|---|
| Durasi | Menit (misal: 90) |
| Acak Urutan Soal | Ya / Tidak |
| Acak Opsi Jawaban | Ya / Tidak (khusus PG) |
| Tampilan Soal | Satu per halaman / Semua dalam satu halaman |
| Tampilkan Hasil | Setelah selesai / Setelah dikoreksi |
| Tampilkan Pembahasan | Ya / Tidak (setelah ujian) |
| Bobot Nilai | Ikuti bobot per soal / Semua sama |

### Step 4: Jadwal & Keamanan

| Aspek | Konfigurasi |
|---|---|
| **Jadwal** | Tanggal, jam mulai, jam selesai |
| **Batas Perangkat** | 1 device per siswa (deteksi fingerprint) |
| **Fullscreen Wajib** | Ya/Tidak — keluar = peringatan |
| **Deteksi Pindah Tab** | Catat frekuensi, batas 3x → auto-submit |
| **Cegah Copy-Paste** | Ya/Tidak |
| **Batas Toleransi** | Maksimal 3x keluar fullscreen/tab |
| **Status** | Draft / Published |

---

## 4. Keamanan Ujian

| Mekanisme | Implementasi Teknis |
|---|---|
| **Acak Soal** | `ORDER BY RANDOM()` dengan seed per sesi |
| **Acak Opsi** | Opsi diacak render, kunci tetap mengikuti |
| **Batas Perangkat** | Token sesi + browser fingerprint hash |
| **Fullscreen** | `requestFullscreen()` + `fullscreenchange` listener |
| **Deteksi Tab** | `visibilitychange` → counter + notifikasi |
| **Blokir Klik Kanan** | `contextmenu` prevent default |
| **Blokir Shortcut** | Cegah Ctrl+C/V/U, F12, PrintScreen |
| **Timer Server-Side** | Backend lacak `started_at`, hitung sisa waktu |
| **Auto-Submit** | Cron job setiap menit: submit sesi yang `time_left <= 0` |
| **IP Logging** | Catat `ip_address` saat start & submit |

**Filosofi**: Keamanan ringan — mencegah kecurangan kasual. Ujian online tidak menggantikan pengawasan fisik.

---

## 5. Koreksi Otomatis & Manual

### 5.1 Koreksi Otomatis

| Tipe | Metode |
|---|---|
| PG, PGK, BS, Jodoh | Exact match |
| Isian Singkat | Case-insensitive trim + Levenshtein ≤1 → **flag "review"** |

```php
// Fuzzy match untuk Isian Singkat
function checkIsian($jawaban, $kunci) {
    if (strtolower(trim($jawaban)) === strtolower(trim($kunci))) return true;
    if (levenshtein(strtolower(trim($jawaban)), strtolower(trim($kunci))) <= 1)
        return 'review';  // flag untuk review guru
    return false;
}
```

### 5.2 Koreksi Manual — Esai

```
┌──────────────────────────────────────────────────────────┐
│  Koreksi Esai — STS B.Indo 7A   Soal 3 dari 20          │
│  ⬅️ 7/35 siswa dikoreksi                                │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Pertanyaan (Bobot: 5):                                  │
│  Jelaskan perbedaan fakta dan opini, beri contoh!        │
│                                                          │
│  👨‍🎓 Adi Pratama:                                       │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Fakta = pernyataan yang dapat dibuktikan.         │   │
│  │ Contoh: "Suhu matahari 5.500°C"                   │   │
│  │ Opini = pendapat pribadi.                         │   │
│  │ Contoh: "Matahari terlihat indah saat terbenam"   │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  Skor: [___]/5   Catatan: [________________________]    │
│                                                          │
│  [← Sebelumnya]  [💾 Simpan & Lanjut →]                 │
└──────────────────────────────────────────────────────────┘
```

**Mode koreksi esai:**
- **Per Soal**: semua jawaban 1 soal → lebih cepat, konsisten
- **Per Siswa**: semua jawaban 1 siswa → review holistik
- Rubrik penilaian opsional per soal
- Shortcut keyboard: 0-5 untuk skor cepat

---

## 6. Monitoring Ujian (Real-time via WebSocket)

```
┌─────────────────────────────────────────────────────────────┐
│  Monitoring: STS B.Indonesia — 7A      ⏱ 28:15 tersisa     │
│  🟢 On=24  ✅ Selesai=8  🟡 Idle=2  ❌ DC=1               │
├─────────────────────────────────────────────────────────────┤
│ ┌────┬──────────┬────────┬──────────┬────────┬──────────┐  │
│ │ No │ Nama     │ Status │ Progress │ Waktu  │ Aksi     │  │
│ ├────┼──────────┼────────┼──────────┼────────┼──────────┤  │
│ │ 1  │ Adi      │ 🟢 On  │ 15/20    │ 28:15  │ 👁 ⚠️    │  │
│ │ 2  │ Budi     │ 🟢 On  │ 12/20    │ 27:50  │ 👁 ⚠️    │  │
│ │ 3  │ Citra    │ ✅ Sel │ 20/20    │ 25:10  │ 📊       │  │
│ │ 4  │ Dina     │ 🟡 Idle│ 5/20     │ 15:30  │ ⚠️ Kirim │  │
│ │ 5  │ Eko      │ ❌ DC  │ 8/20     │ —      │ 🔄 Reset │  │
│ └────┴──────────┴────────┴──────────┴────────┴──────────┘  │
│                                                             │
│  [🔄 Refresh]  [⏹ Akhiri Semua Paksa]  [📊 Hasil]          │
└─────────────────────────────────────────────────────────────┘
```

Status real-time: siswa pindah soal → progress terupdate di dashboard guru via WebSocket.
