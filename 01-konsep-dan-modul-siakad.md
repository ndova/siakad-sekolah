# Konsep & Daftar Modul SIAKAD SMP & SMK
## Berbasis Kurikulum Merdeka (Kurikulum Nasional 2024)

---

## 1. Ringkasan Tujuan Sistem

**SIAKAD (Sistem Informasi Akademik) Sekolah** adalah platform terpadu berbasis web yang dirancang untuk mendigitalisasi seluruh proses akademik dan administrasi di jenjang **SMP** dan **SMK**. Sistem ini dibangun di atas fondasi **Kurikulum Merdeka** (ditetapkan sebagai Kurikulum Nasional sejak 2024) dan mencakup tiga pilar utama:

| Pilar | Cakupan |
|---|---|
| **Akademik** | Kurikulum & CP/TP/ATP, jadwal, presensi, penilaian intrakurikuler, Projek P5, rapor, ujian online |
| **Keuangan** | Administrasi SPP, iuran sekolah, pembayaran digital, pelaporan keuangan |
| **Portal** | Portal khusus siswa & orang tua/wali (frontend publik) vs Backend internal untuk admin, guru, wali kelas, bendahara, kepala sekolah |

**Tujuan utama:**
- Menyediakan **single source of truth** data akademik dan keuangan sekolah.
- Mendukung implementasi **Kurikulum Merdeka** secara penuh: pembelajaran intrakurikuler terdiferensiasi, Projek Penguatan Profil Pelajar Pancasila (P5), serta penilaian formatif-summatif dan rapor.
- Memudahkan **kolaborasi** antara sekolah, guru, siswa, dan orang tua melalui portal yang sesuai peran.
- Mengakomodasi kekhususan **SMK**: PKL (Praktik Kerja Lapangan), UKK (Uji Kompetensi Keahlian), Teaching Factory, dan sertifikasi kompetensi.

---

## 2. Daftar Modul Utama

```
┌──────────────────────────────────────────────────────────────────┐
│                        SIAKAD SMP & SMK                          │
├──────────────────────────────────────────────────────────────────┤
│  A. DATA MASTER                                                  │
│  B. KURIKULUM & CAPAIAN PEMBELAJARAN (CP)                        │
│  C. PENJADWALAN                                                  │
│  D. PROJEK P5 (Projek Penguatan Profil Pelajar Pancasila)        │
│  E. PENILAIAN & RAPOR                                            │
│  F. PRESENSI (Kehadiran)                                         │
│  G. EXAM / UJIAN ONLINE                                          │
│  H. PEMBAYARAN (SPP & Iuran Sekolah)                             │
│  I. LAPORAN & ANALITIK                                           │
│  J. USER MANAGEMENT & ROLE (RBAC)                                │
│  K. KOMUNIKASI & PENGUMUMAN                                      │
│  L. EKSTRAKURIKULER                                              │
│  M. PERPUSTAKAAN DIGITAL                                         │
│  N. BIMBINGAN KONSELING (BK)                                     │
│  O. PPDB (Penerimaan Peserta Didik Baru)                         │
│  P. KHUSUS SMK: PKL, UKK, Teaching Factory, Sertifikasi          │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3. Penjelasan Singkat Per Modul

### A. DATA MASTER
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Mengelola seluruh data referensi dan entitas inti sistem: data sekolah, tahun ajaran, semester, rombongan belajar (kelas), data siswa, data guru/karyawan, mata pelajaran, kompetensi keahlian (SMK), dan konfigurasi global. |
| **Pengguna Utama** | Admin, Kepala Sekolah |
| **Sub-modul** | Tahun Ajaran, Semester, Rombel, Siswa, GTK (Guru & Tenaga Kependidikan), Mapel, Kompetensi Keahlian (SMK), Konfigurasi Umum |

### B. KURIKULUM & CAPAIAN PEMBELAJARAN (CP)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Mengelola struktur kurikulum sesuai Kurikulum Merdeka: Capaian Pembelajaran (CP) per fase, Tujuan Pembelajaran (TP), Alur Tujuan Pembelajaran (ATP), Kriteria Ketercapaian Tujuan Pembelajaran (KKTP), modul ajar, dan pemetaan mapel per rombel. Mendukung fleksibilitas pembelajaran terdiferensiasi. |
| **Pengguna Utama** | Waka Kurikulum, Guru |
| **Sub-modul** | CP per Fase (D untuk SMP, E untuk SMK), TP/ATP, KKTP, Modul Ajar, Pemetaan Mapel-Rombel, Struktur Kurikulum |

### C. PENJADWALAN
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Membuat dan mengelola jadwal pelajaran mingguan per rombel, termasuk alokasi jam intrakurikuler dan P5. Mendukung bentrok detection, alokasi ruang, dan publikasi jadwal ke portal siswa/guru. |
| **Pengguna Utama** | Waka Kurikulum, Admin |
| **Sub-modul** | Generator Jadwal, Alokasi Guru-Mapel-Rombel, Manajemen Ruang, Kalender Akademik, Publikasi Jadwal |

### D. PROJEK P5 (Projek Penguatan Profil Pelajar Pancasila)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Mengelola siklus penuh Projek P5: penetapan tema (6 tema dari Kemendikbudristek), pembentukan kelompok fasilitator & siswa, perencanaan modul projek, log aktivitas/asesmen formatif, penilaian dimensi Profil Pelajar Pancasila, hingga rapor P5 terpisah. |
| **Pengguna Utama** | Koordinator P5, Fasilitator/Guru, Wali Kelas |
| **Sub-modul** | Tema & Topik P5, Kelompok Projek, Modul Projek, Jurnal Aktivitas, Asesmen Dimensi PPP, Rapor P5 |

### E. PENILAIAN & RAPOR
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Input dan olah nilai intrakurikuler: penilaian formatif (harian/PH), sumatif (PTS/PAS), sumatif akhir jenjang, nilai sikap, serta ekstrakurikuler. Generate rapor intrakurikuler dan rapor P5 sesuai template Kemendikbudristek. Termasuk kenaikan kelas dan kelulusan. |
| **Pengguna Utama** | Guru (input nilai), Wali Kelas (validasi & cetak rapor), Kepala Sekolah (approval) |
| **Sub-modul** | Input Nilai Formatif, Input Nilai Sumatif, Nilai Sikap, Deskripsi Capaian, Generate Rapor Intrakurikuler, Generate Rapor P5, Kenaikan Kelas, Kelulusan |

### F. PRESENSI (Kehadiran)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Pencatatan kehadiran harian siswa dan guru. Mendukung multi-metode: manual oleh wali kelas, scan QR, atau input via portal guru. Rekap kehadiran bulanan/semester untuk dilampirkan di rapor. |
| **Pengguna Utama** | Wali Kelas, Guru Piket, Guru Mapel |
| **Sub-modul** | Presensi Harian (Siswa & Guru), QR Code Presensi, Izin/Sakit/Alfa, Rekap Kehadiran Bulanan/Semester |

### G. EXAM / UJIAN ONLINE
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Platform Computer-Based Test (CBT) terintegrasi: bank soal (multi-tipe: pilihan ganda, esai, menjodohkan, true/false), pembuatan paket ujian, penjadwalan ujian, pengawasan (proctoring dasar via browser lock/shuffle soal), pengerjaan oleh siswa secara online, koreksi otomatis (PG) dan manual (esai), serta analisis butir soal (tingkat kesukaran, daya beda). |
| **Pengguna Utama** | Guru (buat soal & ujian), Siswa (mengerjakan), Admin (konfigurasi) |
| **Sub-modul** | Bank Soal, Paket Ujian, Jadwal Ujian, Ruang Ujian Online, Monitoring Ujian Real-time, Koreksi Otomatis & Manual, Analisis Butir Soal, Hasil Ujian |

### H. PEMBAYARAN (SPP & Iuran Sekolah)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Mengelola seluruh tagihan dan pembayaran siswa: SPP bulanan, iuran tahunan, biaya seragam, biaya kegiatan, dll. Mendukung multi-channel pembayaran (virtual account, QRIS, transfer bank, cash/offline). Notifikasi tagihan, riwayat pembayaran, laporan kas, dan integrasi akuntansi dasar. |
| **Pengguna Utama** | Bendahara, Orang Tua/Wali (via portal), Kepala Sekolah (monitoring) |
| **Sub-modul** | Master Tarif & Golongan, Generate Tagihan Massal, Pembayaran Online (VA/QRIS), Pembayaran Offline/Tunai, Notifikasi Jatuh Tempo, Riwayat & Kwitansi, Laporan Kas Harian/Bulanan |

### I. LAPORAN & ANALITIK
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Dashboard dan report generasi untuk monitoring KPI akademik dan keuangan. Visualisasi data: tren nilai, perbandingan capaian per kelas/mapel, tingkat kehadiran, collection rate SPP, analisis P5, dan lain-lain. Akses berbeda per role (dashboard kepala sekolah vs guru vs bendahara). |
| **Pengguna Utama** | Kepala Sekolah (dashboard eksekutif), Waka Kurikulum, Bendahara, Admin |
| **Sub-modul** | Dashboard Kepala Sekolah, Laporan Akademik, Laporan Keuangan, Analisis Presensi, Export Data (PDF/Excel), Data Dapodik Export |

### J. USER MANAGEMENT & ROLE (RBAC)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Manajemen pengguna berbasis Role-Based Access Control (RBAC). Setiap role memiliki permission granular terhadap modul dan aksi (CRUD). Mendukung hierarki akses per rombel/wali kelas. |
| **Pengguna Utama** | Admin, Kepala Sekolah (approval role) |
| **Role Utama** | Super Admin, Admin, Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Guru Mapel, Wali Kelas, Bendahara, BK, Siswa, Orang Tua/Wali |

### K. KOMUNIKASI & PENGUMUMAN
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Pengumuman sekolah, pesan massal ke siswa/orang tua per rombel/angkatan, notifikasi otomatis (tagihan, jadwal ujian, deadline P5), dan log komunikasi. |
| **Pengguna Utama** | Admin, Wali Kelas, Kepala Sekolah |
| **Sub-modul** | Pengumuman, Notifikasi Push/Email/WA Gateway, Broadcast per Rombel |

### L. EKSTRAKURIKULER
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Pendaftaran ekskul oleh siswa, pengelolaan jadwal ekskul, absensi ekskul, dan penilaian ekskul yang masuk ke rapor. |
| **Pengguna Utama** | Waka Kesiswaan, Pembina Ekskul, Siswa |

### M. PERPUSTAKAAN DIGITAL
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Katalog buku, peminjaman & pengembalian, denda keterlambatan, riwayat baca siswa. (Opsional, bisa fase 2) |
| **Pengguna Utama** | Pustakawan, Siswa, Guru |

### N. BIMBINGAN KONSELING (BK)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Pencatatan kasus/kedisiplinan, konseling individual/kelompok, home visit, dan laporan BK per siswa. |
| **Pengguna Utama** | Guru BK, Wali Kelas, Kepala Sekolah |

### O. PPDB (Penerimaan Peserta Didik Baru)
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Portal pendaftaran online, verifikasi berkas, seleksi, pengumuman hasil, daftar ulang, dan migrasi data calon siswa ke data master. |
| **Pengguna Utama** | Panitia PPDB, Calon Siswa & Orang Tua, Admin |

### P. KHUSUS SMK: PKL, UKK, Teaching Factory, Sertifikasi
| Aspek | Keterangan |
|---|---|
| **Fungsi Utama** | Mengelola Praktik Kerja Lapangan (PKL/Prakerin): penempatan DU/DI, monitoring jurnal harian, penilaian pembimbing. Uji Kompetensi Keahlian (UKK): pendaftaran, penjadwalan, penilaian. Teaching Factory: manajemen produk/jasa, penjualan. Sertifikasi kompetensi LSP. |
| **Pengguna Utama** | Waka Humas/Hubin, Kaprodi, Guru Pembimbing, Asesor |

---

## 4. Pembagian Portal: Frontend (Siswa/Orang Tua) vs Backend Internal

### PORTAL SISWA & ORANG TUA/WALI (Frontend Publik)

```
┌───────────────────────────────────────────────────────┐
│              PORTAL SISWA & ORANG TUA                 │
├───────────────────────────────────────────────────────┤
│  📊 Dashboard Pribadi                                 │
│     • Ringkasan akademik & tagihan                     │
│     • Notifikasi terbaru                               │
│                                                       │
│  📅 Jadwal Pelajaran                                  │
│     • Lihat jadwal mingguan                            │
│                                                       │
│  📋 Presensi                                          │
│     • Riwayat kehadiran per semester                   │
│                                                       │
│  📝 Nilai & Rapor                                     │
│     • Lihat nilai (formatif & sumatif)                 │
│     • Lihat & download rapor intrakurikuler            │
│     • Lihat & download rapor P5                        │
│                                                       │
│  🎯 Projek P5                                         │
│     • Lihat kelompok & tema                            │
│     • Lihat progress & penilaian P5                    │
│                                                       │
│  💻 Ujian Online                                      │
│     • Akses ruang ujian                                │
│     • Mengerjakan soal                                 │
│     • Lihat hasil ujian                                │
│                                                       │
│  💰 Pembayaran                                        │
│     • Lihat daftar tagihan                             │
│     • Bayar via VA/QRIS/transfer                       │
│     • Riwayat pembayaran & download kwitansi           │
│                                                       │
│  📢 Pengumuman                                        │
│     • Baca pengumuman sekolah                          │
│                                                       │
│  ⭐ Ekstrakurikuler                                   │
│     • Lihat daftar & jadwal ekskul                     │
│     • Daftar ekskul                                    │
│                                                       │
│  👤 Profil                                            │
│     • Data diri siswa/orang tua                        │
│     • Ganti password                                   │
│                                                       │
│  (Khusus SMK) 🏭 PKL                                  │
│     • Jurnal harian PKL                                │
│     • Lihat penilaian pembimbing                       │
└───────────────────────────────────────────────────────┘
```

### BACKEND INTERNAL (Panel Admin, Guru, Wali Kelas, Bendahara, Kepala Sekolah)

```
┌──────────────────────────────────────────────────────────────────┐
│                     BACKEND INTERNAL                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  👑 SUPER ADMIN / ADMIN                                         │
│     • Data Master (semua entitas)                                │
│     • User & Role Management                                     │
│     • Konfigurasi sistem                                         │
│     • PPDB management                                            │
│                                                                  │
│  📐 WAKA KURIKULUM                                              │
│     • Kurikulum & CP/TP/ATP                                      │
│     • Pemetaan mapel & struktur kurikulum                        │
│     • Penjadwalan                                                │
│     • Kalender akademik                                          │
│                                                                  │
│  👨‍🏫 GURU MAPEL                                                  │
│     • Input nilai (formatif, sumatif)                             │
│     • Bank soal & buat ujian online                              │
│     • Monitoring ujian                                           │
│     • Koreksi jawaban                                            │
│     • Input presensi di kelas                                    │
│     • Lihat jadwal mengajar                                      │
│                                                                  │
│  🧑‍💼 WALI KELAS                                                  │
│     • Rekap & validasi presensi siswa                            │
│     • Validasi nilai seluruh mapel                               │
│     • Generate & cetak rapor                                     │
│     • Input nilai sikap                                          │
│     • Manajemen kenaikan kelas                                   │
│     • Broadcast ke wali murid                                    │
│                                                                  │
│  🎨 KOORDINATOR P5 / FASILITATOR                                │
│     • Setup tema & topik P5                                      │
│     • Bentuk kelompok fasilitator & siswa                        │
│     • Buat modul projek                                          │
│     • Input jurnal aktivitas                                     │
│     • Penilaian dimensi Profil Pelajar Pancasila                 │
│     • Generate rapor P5                                          │
│                                                                  │
│  💵 BENDAHARA                                                   │
│     • Setup tarif & golongan                                     │
│     • Generate tagihan massal                                    │
│     • Input pembayaran offline/tunai                             │
│     • Verifikasi pembayaran online                               │
│     • Cetak kwitansi                                             │
│     • Laporan kas harian/bulanan                                 │
│     • Rekap tunggakan                                            │
│                                                                  │
│  📊 KEPALA SEKOLAH                                              │
│     • Dashboard eksekutif (akademik & keuangan)                  │
│     • Approval rapor                                             │
│     • Approval kenaikan kelas & kelulusan                        │
│     • Monitoring kinerja guru                                    │
│     • Laporan & analitik penuh                                   │
│     • Export Dapodik                                              │
│                                                                  │
│  🧠 GURU BK                                                      │
│     • Catatan kasus & konseling                                  │
│     • Laporan BK per siswa                                       │
│                                                                  │
│  🏅 WAKA KESISWAAN                                              │
│     • Manajemen ekstrakurikuler                                  │
│     • Penilaian ekskul                                           │
│                                                                  │
│  (Khusus SMK) 🏭 KAPRODI / GURU PEMBIMBING                     │
│     • Manajemen PKL: DU/DI, penempatan, monitoring jurnal,       │
│       penilaian                                                  │
│     • UKK: pendaftaran, jadwal, penilaian                        │
│     • Teaching Factory: manajemen produk/jasa                    │
│     • Sertifikasi LSP                                             │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 5. Matriks Ringkasan: Modul × Role × Portal

| Modul | Portal Siswa/Ortu | Backend Admin | Guru Mapel | Wali Kelas | Bendahara | Kepsek | Waka Kurikulum | BK | Koord. P5 |
|---|---|---|---|---|---|---|---|---|---|
| **Data Master** | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Kurikulum & CP** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Penjadwalan** | ✅ (lihat) | ✅ | ✅ (lihat) | ✅ (lihat) | ❌ | ✅ (lihat) | ✅ | ❌ | ❌ |
| **Projek P5** | ✅ (progress) | ✅ | ✅ (fasilitator) | ❌ | ❌ | ✅ (monitor) | ❌ | ❌ | ✅ |
| **Penilaian & Rapor** | ✅ (lihat) | ❌ | ✅ (input) | ✅ (validasi) | ❌ | ✅ (approval) | ❌ | ❌ | ✅ (P5) |
| **Presensi** | ✅ (lihat) | ❌ | ✅ (input) | ✅ (rekap) | ❌ | ✅ (monitor) | ❌ | ❌ | ❌ |
| **Exam Online** | ✅ (kerjakan) | ✅ (config) | ✅ (buat+koreksi) | ❌ | ❌ | ✅ (monitor) | ❌ | ❌ | ❌ |
| **Pembayaran** | ✅ (bayar+riwayat) | ❌ | ❌ | ❌ | ✅ | ✅ (monitor) | ❌ | ❌ | ❌ |
| **Laporan & Analitik** | ❌ | ✅ | ✅ (terbatas) | ✅ (terbatas) | ✅ (keuangan) | ✅ | ✅ | ✅ | ❌ |
| **User & Role** | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Komunikasi** | ✅ (terima) | ✅ | ❌ | ✅ (kirim) | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Ekstrakurikuler** | ✅ (daftar) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Perpustakaan** | ✅ (pinjam) | ✅ | ❌ | ❌ | ❌ | ✅ (monitor) | ❌ | ❌ | ❌ |
| **BK** | ❌ | ❌ | ❌ | ✅ (lihat) | ❌ | ✅ (monitor) | ❌ | ✅ | ❌ |
| **PPDB** | ✅ (daftar) | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **PKL/UKK (SMK)** | ✅ (jurnal) | ✅ | ✅ (pembimbing) | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |

---

## 6. Catatan Arsitektur & Teknis (Rekomendasi Awal)

| Aspek | Rekomendasi |
|---|---|
| **Arsitektur** | Monorepo dengan backend API (RESTful) + 2 frontend apps (Portal Siswa/Ortu & Backend Internal) |
| **Backend** | Laravel / Node.js (NestJS) / Go — modular monolith, siap scale ke microservices |
| **Frontend** | React (Next.js) / Vue (Nuxt) dengan component library modern (shadcn/ui, Ant Design, atau MUI) |
| **Database** | PostgreSQL (relasional) + Redis (cache/session/queue) |
| **File Storage** | MinIO / S3-compatible untuk dokumen rapor, soal, bukti bayar |
| **Auth** | JWT + RBAC granular, SSO opsional, session timeout terpisah untuk portal vs backend |
| **Payment Gateway** | Midtrans / Xendit / Duitku untuk VA, QRIS, dan e-wallet |
| **Realtime** | WebSocket (Pusher/Laravel Reverb/Socket.io) untuk monitoring ujian dan notifikasi |
| **Mobile** | PWA (Progressive Web App) untuk portal siswa/orang tua, opsional native app di fase lanjut |
| **Deployment** | Docker + Kubernetes atau VPS; CI/CD dengan GitHub Actions |

---

## 7. Rekomendasi Fase Implementasi

| Fase | Modul | Alasan |
|---|---|---|
| **Fase 1 (MVP)** | Data Master, User & Role, Kurikulum & CP, Penilaian & Rapor, Presensi, Pembayaran, Portal Siswa/Ortu dasar | Modul inti wajib untuk operasional harian |
| **Fase 2** | Penjadwalan, Projek P5, Exam Online, Laporan & Analitik dasar | Kurikulum Merdeka lengkap dan ujian digital |
| **Fase 3** | Komunikasi & Pengumuman, Ekstrakurikuler, BK, Laporan lanjutan (Dapodik) | Pelengkap ekosistem sekolah |
| **Fase 4** | PPDB, Perpustakaan Digital, SMK (PKL, UKK, Teaching Factory) | Ekspansi dan spesialisasi SMK |

---

Dokumen ini adalah titik awal perancangan. Langkah selanjutnya: detilkan spesifikasi tiap modul, rancang skema database, dan mulai MVP Fase 1.
