# Database Terpadu SIAKAD — Daftar Tabel & Field Kunci

> Mencakup seluruh modul: Master, Siswa/Ortu, Kurikulum, Akademik, Presensi, Exam, Pembayaran

---

## 1. ERD Global (Ringkasan)

```
                        ┌──────────────┐
                        │   schools    │
                        └──────┬───────┘
                               │
          ┌────────────────────┼────────────────────┐
          ▼                    ▼                    ▼
   ┌─────────────┐    ┌──────────────┐     ┌──────────────┐
   │academic_years│    │   majors     │     │  subjects    │
   └──────┬──────┘    └──────┬───────┘     └──────┬───────┘
          │                  │                    │
          ▼                  ▼                    ▼
   ┌─────────────┐    ┌──────────────┐     ┌──────────────┐
   │  semesters  │    │   classes    │────▶│ class_subject│
   └──────┬──────┘    └──────┬───────┘     └──────────────┘
          │                  │
          │         ┌────────┴────────┐
          │         ▼                 ▼
          │  ┌─────────────┐   ┌──────────────┐
          │  │  students   │◀──│    parents   │
          │  └──────┬──────┘   └──────────────┘
          │         │                 │
          │         │    parent_student (pivot)
          │         │
          ├─────────┼──────────────────────────────┐
          │         │         AKADEMIK             │
          │         ├────────┬─────────┬───────────┤
          │         ▼        ▼         ▼           ▼
          │  ┌──────────┐ ┌────────┐ ┌──────────────┐
          │  │  grades  │ │reports │ │ p5_projects  │
          │  └──────────┘ └────────┘ └──────────────┘
          │                               │
          │                        ┌──────▼───────┐
          │                        │p5_assessments│
          │                        └──────────────┘
          │
          ├─────────────────────────────────────────┐
          │              EXAM                       │
          ├────────┬────────┬──────────┬────────────┤
          ▼        ▼        ▼          ▼            ▼
   ┌──────────┐ ┌────────┐ ┌──────────────┐ ┌──────────────┐
   │question_ │ │ exams  │ │exam_sessions │ │ exam_results │
   │ banks    │ └────────┘ └──────────────┘ └──────────────┘
   └────┬─────┘      │           │
        ▼            ▼           ▼
   ┌──────────┐ ┌──────────────┐ ┌──────────────┐
   │questions │ │exam_questions│ │ exam_answers │
   └──────────┘ └──────────────┘ └──────────────┘
          │
          ├─────────────────────────────────────────┐
          │           PEMBAYARAN                    │
          ├──────────┬──────────┬───────────────────┤
          ▼          ▼          ▼                   ▼
   ┌──────────┐ ┌────────┐ ┌──────────────┐ ┌──────────────┐
   │fee_types │ │invoices│ │  payments    │ │payment_methods│
   └────┬─────┘ └───┬────┘ └──────────────┘ └──────────────┘
        │           │
        ▼           ▼
 ┌──────────────┐ ┌──────────────┐
 │fee_type_     │ │invoice_items │
 │targets       │ └──────────────┘
 └──────────────┘
```

---

## 2. Semua Tabel & Field Kunci

### ⬛ BLOK A: DATA MASTER

#### `schools`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `name` | VARCHAR(200) | Nama sekolah |
| `npsn` | VARCHAR(20) | Nomor Pokok Sekolah Nasional |
| `address` | TEXT | |
| `phone` | VARCHAR(30) | |
| `email` | VARCHAR(100) | |
| `logo` | VARCHAR(255) | Path logo |
| `is_active` | BOOLEAN | |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `academic_years`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `code` | VARCHAR(9) | "2025/2026" |
| `start_date` | DATE | |
| `end_date` | DATE | |
| `is_active` | BOOLEAN | Tahun ajaran berjalan |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `semesters`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `academic_year_id` | UUID FK → academic_years | |
| `semester_number` | SMALLINT | 1 = Ganjil, 2 = Genap |
| `start_date` | DATE | |
| `end_date` | DATE | |
| `is_active` | BOOLEAN | |

#### `majors` (Jurusan — untuk SMK)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `code` | VARCHAR(10) | "TKJ", "AKL", "OTKP" |
| `name` | VARCHAR(100) | "Teknik Komputer Jaringan" |
| `is_active` | BOOLEAN | |

#### `classes` (Rombel)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `academic_year_id` | UUID FK → academic_years | |
| `major_id` | UUID FK → majors, nullable | |
| `code` | VARCHAR(20) | "7A", "10-TKJ-1" |
| `tingkat` | SMALLINT | 7–12 |
| `jenjang` | VARCHAR(5) | SMP/SMA/SMK |
| `wali_kelas_id` | UUID FK → users, nullable | |
| `is_active` | BOOLEAN | |

#### `subjects` (Mapel)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `code` | VARCHAR(20) | "BIG", "MTK", "PAI" |
| `name` | VARCHAR(150) | "Bahasa Inggris" |
| `kategori` | VARCHAR(20) | umum/kejuruan/muatan_lokal |
| `is_active` | BOOLEAN | |

#### `class_subject` (Mapel per Kelas — Pivot)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `class_id` | UUID FK → classes | |
| `subject_id` | UUID FK → subjects | |
| `teacher_id` | UUID FK → users | Guru pengampu |
| `kkm` | DECIMAL(5,2) | KKM default |
| `jam_per_minggu` | SMALLINT | |

---

### ⬛ BLOK B: USER, SISWA & ORANG TUA

#### `users`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools, nullable | null = superadmin |
| `name` | VARCHAR(200) | |
| `email` | VARCHAR(100) UNIQUE | |
| `password` | VARCHAR(255) | bcrypt |
| `role` | VARCHAR(30) | admin, guru, walikelas, bendahara, kepsek, siswa, orang_tua |
| `nip` | VARCHAR(30) nullable | NIP guru/staff |
| `phone` | VARCHAR(20) | |
| `photo` | VARCHAR(255) | |
| `is_active` | BOOLEAN | |
| `last_login_at` | TIMESTAMP | |
| `fcm_token` | VARCHAR(255) | Push notification |

#### `students`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `user_id` | UUID FK → users, unique | Link ke user login (siswa) |
| `school_id` | UUID FK → schools | |
| `class_id` | UUID FK → classes | Rombel saat ini |
| `nisn` | VARCHAR(10) UNIQUE | Nomor Induk Siswa Nasional |
| `nis` | VARCHAR(20) | Nomor Induk Sekolah |
| `nama_lengkap` | VARCHAR(200) | |
| `jk` | CHAR(1) | L / P |
| `tempat_lahir` | VARCHAR(100) | |
| `tanggal_lahir` | DATE | |
| `agama` | VARCHAR(20) | |
| `alamat` | TEXT | |
| `nama_ayah` | VARCHAR(200) | |
| `nama_ibu` | VARCHAR(200) | |
| `status` | VARCHAR(20) | aktif, lulus, pindah, keluar |
| `tanggal_masuk` | DATE | |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `parents` (Orang Tua / Wali)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `user_id` | UUID FK → users, unique | Link ke user login (ortu) |
| `nama_lengkap` | VARCHAR(200) | |
| `jk` | CHAR(1) | |
| `hubungan` | VARCHAR(20) | ayah, ibu, wali |
| `pekerjaan` | VARCHAR(100) | |
| `phone` | VARCHAR(20) | |
| `alamat` | TEXT | |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `parent_student` (Pivot — satu ortu bisa punya >1 anak)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `parent_id` | UUID FK → parents | |
| `student_id` | UUID FK → students | |
| `is_primary` | BOOLEAN DEFAULT false | Wali utama |

---

### ⬛ BLOK C: KURIKULUM & CP/TP (Kurikulum Merdeka)

#### `curricula`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `name` | VARCHAR(150) | "Kurikulum Merdeka 2025" |
| `academic_year_id` | UUID FK → academic_years | |
| `is_active` | BOOLEAN | |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `learning_outcomes` — CP (Capaian Pembelajaran)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `curriculum_id` | UUID FK → curricula | |
| `subject_id` | UUID FK → subjects | |
| `phase` | VARCHAR(5) | A, B, C, D, E, F |
| `code` | VARCHAR(30) | CP-BIG-D-01 |
| `description` | TEXT | Deskripsi CP |
| `urutan` | SMALLINT | |

#### `learning_objectives` — TP (Tujuan Pembelajaran)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `learning_outcome_id` | UUID FK → learning_outcomes | CP induk |
| `code` | VARCHAR(30) | TP-BIG-D-01.1 |
| `description` | TEXT | |
| `level_kognitif` | VARCHAR(5) | L1, L2, L3 |
| `urutan` | SMALLINT | |

#### `learning_objective_subjects` — Mapping TP ke Mapel per Kelas (ATP)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `learning_objective_id` | UUID FK → learning_objectives | |
| `class_subject_id` | UUID FK → class_subject | |
| `semester_id` | UUID FK → semesters | Semester diajarkan |
| `urutan_ajar` | SMALLINT | Urutan dalam semester |

---

### ⬛ BLOK D: NILAI & RAPOR

#### `grades` (Nilai per TP per Siswa)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `student_id` | UUID FK → students | |
| `class_subject_id` | UUID FK → class_subject | Mapel di kelas |
| `learning_objective_id` | UUID FK → learning_objectives | TP yang dinilai |
| `semester_id` | UUID FK → semesters | |
| `jenis_nilai` | VARCHAR(20) | uh, sts, sas, p5, tugas |
| `nilai` | DECIMAL(5,2) | 0–100 |
| `deskripsi` | TEXT | Catatan guru |
| `sumber` | VARCHAR(20) | manual, ujian |
| `exam_result_id` | UUID FK → exam_results, nullable | link ke hasil ujian |
| `created_by` | UUID FK → users | |
| `created_at`, `updated_at` | TIMESTAMP | |

UNIQUE: `(student_id, class_subject_id, learning_objective_id, jenis_nilai)`

#### `reports` (Rapor per Semester)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `student_id` | UUID FK → students | |
| `semester_id` | UUID FK → semesters | |
| `class_subject_id` | UUID FK → class_subject | |
| `nilai_akhir` | DECIMAL(5,2) | Agregasi per mapel |
| `predikat` | VARCHAR(5) | A, B, C, D |
| `deskripsi_cp` | TEXT | Narasi capaian |
| `is_locked` | BOOLEAN DEFAULT false | Final setelah validasi wali kelas |
| `locked_by` | UUID FK → users | |
| `locked_at` | TIMESTAMP | |
| `created_at`, `updated_at` | TIMESTAMP | |

UNIQUE: `(student_id, semester_id, class_subject_id)`

#### `p5_projects` (Proyek Penguatan Profil Pelajar Pancasila)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `semester_id` | UUID FK → semesters | |
| `tema` | VARCHAR(100) | "Kearifan Lokal", "Kewirausahaan" |
| `judul` | VARCHAR(200) | Judul proyek |
| `deskripsi` | TEXT | |
| `class_ids` | JSONB | [UUID kelas] — target kelas |
| `tanggal_mulai` | DATE | |
| `tanggal_selesai` | DATE | |
| `created_by` | UUID FK → users | |

#### `p5_assessments` (Asesmen P5 per Siswa — 6 Dimensi PPP)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `p5_project_id` | UUID FK → p5_projects | |
| `student_id` | UUID FK → students | |
| `dimensi_1` s/d `dimensi_6` | VARCHAR(20) | BB, MB, BSH, SB |
| `catatan_proses` | TEXT | |
| `created_by` | UUID FK → users | |
| `created_at`, `updated_at` | TIMESTAMP | |

---

### ⬛ BLOK E: PRESENSI

#### `attendances` (Presensi Siswa)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `student_id` | UUID FK → students | |
| `class_subject_id` | UUID FK → class_subject | Mapel (null = presensi harian kelas) |
| `semester_id` | UUID FK → semesters | |
| `tanggal` | DATE | |
| `status` | VARCHAR(10) | hadir, izin, sakit, alfa, terlambat |
| `keterangan` | VARCHAR(255) | |
| `created_by` | UUID FK → users | |
| `created_at`, `updated_at` | TIMESTAMP | |

UNIQUE: `(student_id, class_subject_id, tanggal)` — one record per student per day per subject

#### `staff` (Pegawai / Staff)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `user_id` | UUID FK → users, UNIQUE | 1:1 link ke user login |
| `nama_lengkap` | VARCHAR(200) | |
| `nip` | VARCHAR(30) nullable | Nomor Induk Pegawai |
| `nuptk` | VARCHAR(30) nullable | NUPTK khusus guru |
| `jabatan` | VARCHAR(50) | guru, kepsek, bendahara, bk, walikelas, tu, staff, pustakawan, laboran, satpam, kebersihan, admin |
| `golongan` | VARCHAR(10) nullable | III/a, III/b, IV/a, dsb |
| `pendidikan_terakhir` | VARCHAR(100) nullable | |
| `tempat_lahir` | VARCHAR(100) nullable | |
| `tanggal_lahir` | DATE nullable | |
| `jk` | CHAR(1) nullable | L / P |
| `agama` | VARCHAR(20) nullable | |
| `alamat` | TEXT nullable | |
| `phone` | VARCHAR(20) nullable | |
| `photo` | VARCHAR(255) nullable | |
| `tanggal_masuk` | DATE nullable | |
| `is_active` | BOOLEAN default true | |
| `created_at`, `updated_at` | TIMESTAMP | |

Relasi:
- **School (1) ── (N) Staff**
- **User (1) ── (1) Staff**

#### `staff_attendances` (Absensi Pegawai)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `staff_id` | UUID FK → staff | |
| `school_id` | UUID FK → schools | |
| `tanggal` | DATE | |
| `check_in_time` | TIME nullable | Jam masuk (tap-in) |
| `check_out_time` | TIME nullable | Jam pulang (tap-out) |
| `status` | VARCHAR(15) default hadir | hadir, izin, sakit, alfa, terlambat |
| `keterangan` | VARCHAR(255) nullable | |
| `source` | VARCHAR(20) default manual | manual, self_service, mesin_absen |
| `device_sn` | VARCHAR(50) nullable | Serial number mesin fingerprint |
| `created_by` | UUID FK → users nullable | User yang menginput |
| `created_at`, `updated_at` | TIMESTAMP | |

UNIQUE: `(staff_id, tanggal)` — satu pegawai hanya satu record per hari

Relasi:
- **Staff (1) ── (N) StaffAttendance**
- **School (1) ── (N) StaffAttendance**

---

### ⬛ BLOK F: EXAM / UJIAN

#### `question_banks`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `name` | VARCHAR(200) | "Bank Soal Bahasa Inggris Kelas 7" |
| `subject_id` | UUID FK → subjects | |
| `created_by` | UUID FK → users | |
| `is_shared` | BOOLEAN DEFAULT false | Bisa dipakai guru lain |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `questions`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `question_bank_id` | UUID FK → question_banks | |
| `learning_objective_id` | UUID FK → learning_objectives | Tag TP |
| `type` | VARCHAR(20) | pg, pg_kompleks, esai, isian_singkat, menjodohkan, benar_salah, uraian |
| `content` | TEXT | Rich text soal (bisa embed img/audio/video) |
| `media` | JSONB | `[{type:"image",url:"..."}]` |
| `options` | JSONB | `[{label:"A",text:"...",is_correct:true}]` |
| `answer_key` | TEXT | Untuk esai / isian singkat |
| `score` | DECIMAL(5,2) | Bobot nilai soal |
| `level_kognitif` | VARCHAR(5) | L1/L2/L3 |
| `difficulty` | VARCHAR(10) | mudah/sedang/sulit |
| `created_by` | UUID FK → users | |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `exams` (Paket Ujian)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `code` | VARCHAR(30) | UH-BIG-7A-01 |
| `title` | VARCHAR(200) | "Ulangan Harian 1 — Narrative Text" |
| `type` | VARCHAR(20) | uh, sts, sas, asaj, tryout |
| `subject_id` | UUID FK → subjects | |
| `class_ids` | JSONB | Kelas target |
| `semester_id` | UUID FK → semesters | |
| `start_time` | TIMESTAMP | |
| `end_time` | TIMESTAMP | |
| `duration` | INT | Dalam menit |
| `total_questions` | INT | |
| `total_score` | DECIMAL(6,2) | |
| `random_questions` | BOOLEAN DEFAULT false | Acak urutan soal |
| `random_answers` | BOOLEAN DEFAULT false | Acak opsi jawaban |
| `show_result` | BOOLEAN DEFAULT false | Tampilkan hasil setelah selesai |
| `max_devices` | SMALLINT DEFAULT 1 | |
| `status` | VARCHAR(20) | draft, published, ongoing, finished |
| `created_by` | UUID FK → users | |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `exam_questions` (Pivot Exam ↔ Question)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `exam_id` | UUID FK → exams | |
| `question_id` | UUID FK → questions | |
| `urutan` | SMALLINT | Nomor urut soal |
| `score_override` | DECIMAL(5,2) | null = pakai score dari questions |

#### `exam_sessions` (Sesi Pengerjaan Siswa)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `exam_id` | UUID FK → exams | |
| `student_id` | UUID FK → students | |
| `started_at` | TIMESTAMP | |
| `finished_at` | TIMESTAMP | |
| `remaining_seconds` | INT | Sisa waktu (untuk resume) |
| `status` | VARCHAR(20) | in_progress, submitted, timeout |
| `ip_address` | VARCHAR(45) | |
| `device_info` | VARCHAR(255) | |

#### `exam_answers` (Jawaban Siswa per Soal)
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `exam_session_id` | UUID FK → exam_sessions | |
| `exam_question_id` | UUID FK → exam_questions | |
| `selected_options` | JSONB | `["A","C"]` untuk PG kompleks |
| `text_answer` | TEXT | Untuk esai |
| `is_correct` | BOOLEAN | Auto-graded (null = belum dikoreksi) |
| `score` | DECIMAL(5,2) | Nilai perolehan |
| `created_at`, `updated_at` | TIMESTAMP | |

#### `exam_results`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `exam_session_id` | UUID FK → exam_sessions, unique | |
| `student_id` | UUID FK → students | |
| `exam_id` | UUID FK → exams | |
| `total_score` | DECIMAL(6,2) | |
| `correct_count` | INT | |
| `wrong_count` | INT | |
| `tp_scores` | JSONB | `{"tp-uuid-1":80,"tp-uuid-2":65}` |
| `is_passed` | BOOLEAN | |
| `graded_by` | UUID FK → users, nullable | null = auto-graded |
| `graded_at` | TIMESTAMP | |
| `created_at`, `updated_at` | TIMESTAMP | |

---

### ⬛ BLOK G: PEMBAYARAN

#### `fee_types`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `code` | VARCHAR(30) | SPP_BULANAN, UJIAN_SAS |
| `name` | VARCHAR(150) | |
| `category` | VARCHAR(20) | rutin, tidak_rutin |
| `nominal` | DECIMAL(12,2) | |
| `billing_period` | VARCHAR(10) | monthly, semester, yearly |
| `is_active` | BOOLEAN | |

#### `fee_type_targets`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `fee_type_id` | UUID FK → fee_types | |
| `target_level` | VARCHAR(10) | all, jenjang, tingkat, jurusan |
| `jenjang` | VARCHAR(5) | |
| `tingkat` | SMALLINT | |
| `jurusan_id` | UUID FK → majors | |
| `nominal_override` | DECIMAL(12,2) | |

#### `invoices`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `invoice_number` | VARCHAR(50) UNIQUE | |
| `student_id` | UUID FK → students | |
| `academic_year_id` | UUID FK → academic_years | |
| `semester` | VARCHAR(2) | 1 / 2 |
| `batch_id` | UUID | |
| `status` | VARCHAR(20) | unpaid, partial, paid, overdue, void |
| `subtotal` | DECIMAL(12,2) | |
| `discount` | DECIMAL(12,2) | |
| `total` | DECIMAL(12,2) | |
| `due_date` | DATE | |
| `paid_at` | TIMESTAMP | |
| `created_by` | UUID FK → users | |

#### `invoice_items`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `invoice_id` | UUID FK → invoices | |
| `fee_type_id` | UUID FK → fee_types | |
| `fee_name` | VARCHAR(150) | snapshot |
| `quantity` | SMALLINT | |
| `unit_price` | DECIMAL(12,2) | |
| `subtotal` | DECIMAL(12,2) | |
| `period_month` | SMALLINT | 1–12 |
| `period_year` | SMALLINT | |

#### `payment_methods`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `code` | VARCHAR(30) | CASH, TRANSFER_BCA |
| `name` | VARCHAR(100) | |
| `type` | VARCHAR(20) | offline, online |
| `account_number` | VARCHAR(50) | |
| `account_name` | VARCHAR(100) | |
| `bank_name` | VARCHAR(50) | |
| `is_active` | BOOLEAN | |

#### `payments`
| Field | Type | Keterangan |
|---|---|---|
| `id` | UUID PK | |
| `school_id` | UUID FK → schools | |
| `payment_number` | VARCHAR(50) UNIQUE | |
| `invoice_id` | UUID FK → invoices | |
| `student_id` | UUID FK → students | |
| `paid_by` | VARCHAR(100) | |
| `payment_method_id` | UUID FK → payment_methods | |
| `payment_channel` | VARCHAR(30) | backend, portal, gateway |
| `amount` | DECIMAL(12,2) | |
| `proof_file` | VARCHAR(255) | |
| `gateway_ref` | VARCHAR(100) | |
| `status` | VARCHAR(20) | pending, verified, rejected, void |
| `payment_date` | DATE | |
| `verified_by` | UUID FK → users | |
| `verified_at` | TIMESTAMP | |

---

## 3. Tabel Pendukung

#### `student_class_history` — Riwayat Kelas Siswa
| Field | Type |
|---|---|
| `id` | UUID PK |
| `student_id` | UUID FK → students |
| `class_id` | UUID FK → classes |
| `academic_year_id` | UUID FK → academic_years |
| `semester_id` | UUID FK → semesters |
| `mulai` | DATE |
| `selesai` | DATE |

#### `payment_logs`
| Field | Type |
|---|---|
| `id` | UUID PK |
| `payment_id` | UUID FK → payments |
| `actor_id` | UUID FK → users |
| `action` | VARCHAR(30) |
| `old_status` | VARCHAR(20) |
| `new_status` | VARCHAR(20) |
| `notes` | TEXT |

#### `notifications`
| Field | Type |
|---|---|
| `id` | UUID PK |
| `user_id` | UUID FK → users |
| `type` | VARCHAR(30) |
| `title`, `message` | TEXT |
| `reference_type` | VARCHAR(30) |
| `reference_id` | UUID |
| `is_read` | BOOLEAN DEFAULT false |

---

## 4. Ringkasan Jumlah Tabel

| Blok | Tabel | Jumlah |
|---|---|---|
| A. Master | schools, academic_years, semesters, majors, classes, subjects, class_subject | 7 |
| B. User & Siswa | users, students, parents, parent_student | 4 |
| C. Kurikulum | curricula, learning_outcomes, learning_objectives, learning_objective_subjects | 4 |
| D. Nilai & Rapor | grades, reports, p5_projects, p5_assessments | 4 |
| E. Presensi | attendances, staff, staff_attendances | 3 |
| F. Exam | question_banks, questions, exams, exam_questions, exam_sessions, exam_answers, exam_results | 7 |
| G. Pembayaran | fee_types, fee_type_targets, invoices, invoice_items, payment_methods, payments | 6 |
| Pendukung | student_class_history, payment_logs, notifications | 3 |
| **TOTAL** | | **38 tabel** |
