# Modul Exam — Bagian 2: Portal Siswa, Database, API, Alur Data

---

## 1. Fitur Portal Siswa

### 1.1 List Ujian (3 Tab: Aktif / Terjadwal / Selesai)

Card per ujian menampilkan: nama, mapel, jumlah soal, tanggal, jam, status (🟢 aktif / ⏳ terjadwal / ✅ selesai + nilai). Ujian aktif punya tombol **[▶ Lanjutkan]**; ujian selesai punya **[📊 Lihat Hasil]**.

### 1.2 Tampilan CBT (Fullscreen)

```
┌──────────────────────────────────────────────────────────┐
│  STS B.Indonesia — 7A              ⏱ 42:15  Soal 5/20  │
├──────────────────────────────────────────────────────────┤
│  Pertanyaan (Bobot: 2):                                  │
│                                                          │
│  Bacalah teks berikut! [📷 gambar_teks.png]             │
│  Kalimat fakta adalah nomor...                           │
│                                                          │
│  ○ A. (1) dan (2)   ○ B. (1) dan (3)                    │
│  ● C. (2) dan (4)   ○ D. (3) dan (4)                    │
│  ○ E. (1) dan (4)                                       │
│                                                          │
│  Navigasi: ┌──┬──┬──┬──┬──┬──┬──┬──┬──┬──┐            │
│            │ 1│ 2│ 3│ 4│ 5│ 6│ 7│ 8│ 9│10│            │
│            │✅│✅│✅│✅│🔵│  │  │  │  │  │            │
│            └──┴──┴──┴──┴──┴──┴──┴──┴──┴──┘            │
│  ✅=Dijawab  🔵=Sedang  ○=Belum  🟠=Ragu              │
│                                                          │
│  [← Sebelumnya] [⚑ Ragu] [Selanjutnya →] [📤 Kumpulkan]│
└──────────────────────────────────────────────────────────┘
```

**Fitur CBT:**
- Timer server-side via WebSocket, auto-submit jika habis
- Navigasi grid dengan kode warna
- Auto-save jawaban tiap 15 detik & tiap pindah soal
- Flag ragu-ragu untuk ditinjau ulang
- Fullscreen wajib — keluar = counter peringatan (max 3x)
- Konfirmasi submit: "X soal belum dijawab. Yakin?"
- Peringatan waktu: notifikasi saat 10, 5, 1 menit tersisa
- Resume: disconnect → login lagi → lanjut (timer tetap jalan)

### 1.3 Tampilan Hasil

Header: nilai (0–100), status Tuntas/Remedial (dibandingkan KKTP), benar/salah, waktu.

Detail per soal: nomor + ✅/❌, jawaban siswa, kunci jawaban, pembahasan (jika diaktifkan). Soal esai: "⚠️ Menunggu koreksi guru". Tombol **[🏠 Kembali]**.

---

## 2. Skema Database (8 Tabel)

### 2.1 ER Diagram Ringkas

```
question_banks 1──N questions N──M exams 1──N exam_classes
                                    │
                                    │ 1──N exam_sessions 1──N exam_answers
                                    │              │
                                    │              └── 1──1 exam_results
                                    │
                                    └── (via exam_results.tp_scores) ──► modul Nilai
```

### 2.2 Tabel & Kolom Kunci

#### `question_banks`
| Kolom | Tipe | Ket |
|---|---|---|
| id | UUID PK | |
| school_id, mapel_id | FK | |
| name | VARCHAR | Nama bank soal |
| created_by | FK users | |

#### `questions`
| Kolom | Tipe | Ket |
|---|---|---|
| id | UUID PK | |
| bank_id, mapel_id, tp_id | FK | **tp_id = tagging ke TP Kurikulum** |
| type | ENUM | pg, pgk, bs, jodoh, isian, esai, audio |
| level | ENUM | L1, L2, L3 (LOTS/MOTS/HOTS) |
| difficulty | ENUM | mudah, sedang, sulit |
| question_text | TEXT | Rich HTML (gambar/audio embed) |
| options | JSONB | `[{"label":"A","text":"..."},...]` |
| answer_key | JSONB | `{"correct":"B"}` atau `{"correct":["A","C"]}` |
| bobot | INT | default 1 |
| pembahasan | TEXT | Opsional |
| media_urls | JSONB | `[{"type":"image","url":"...","placement":"stem"}]` |

#### `exams`
| Kolom | Tipe | Ket |
|---|---|---|
| id | UUID PK | |
| name, type, mapel_id | | type: formatif, sts, sas, asaj, ukk, kuis |
| duration_minutes | INT | |
| settings | JSONB | `{randomize_questions, randomize_options, show_per_page, show_result, show_pembahasan, bobot_mode}` |
| security | JSONB | `{fullscreen_required, max_tab_switches, device_limit, block_copy_paste}` |
| start_time, end_time | TIMESTAMP | |
| status | ENUM | draft, published, ongoing, completed, archived |

#### `exam_questions`
| Kolom | Tipe | Ket |
|---|---|---|
| exam_id, question_id | FK (composite unique) | |
| order_number | INT | Urutan (atau seed untuk acak) |
| bobot_override | INT NULL | NULL = ikuti bobot soal asli |

#### `exam_classes`
| Kolom | Tipe | Ket |
|---|---|---|
| exam_id, rombel_id | FK (composite unique) | Kelas peserta ujian |

#### `exam_sessions`
| Kolom | Tipe | Ket |
|---|---|---|
| exam_id, student_id | FK (composite unique) | 1 siswa = 1 sesi per ujian |
| token | VARCHAR(64) UNIQUE | Token sesi |
| status | ENUM | active, submitted, timed_out, terminated |
| started_at, submitted_at, finished_at | TIMESTAMP | |
| ip_address, device_fp | VARCHAR | Audit keamanan |
| tab_switch_count | INT | Akumulasi pindah tab |

#### `exam_answers`
| Kolom | Tipe | Ket |
|---|---|---|
| session_id, question_id | FK (composite unique) | |
| answer_data | JSONB | `{"selected":"B"}`, `{"text":"Jawaban esai..."}` |
| is_correct | BOOLEAN NULL | NULL = esai (pending koreksi) |
| score_achieved | DECIMAL(5,2) | Skor yang didapat |
| is_flagged | BOOL | Ragu-ragu |
| corrected_by, corrected_at | | Untuk koreksi manual |

#### `exam_results`
| Kolom | Tipe | Ket |
|---|---|---|
| exam_id, student_id | FK (composite unique) | |
| session_id | FK UNIQUE | |
| total_score, max_score, nilai_akhir | DECIMAL | nilai_akhir = skala 0–100 |
| **tp_scores** | **JSONB** | **`{"TP-01":{"total":10,"achieved":8,"percent":80},...}`** |
| status | ENUM | pending, partial, graded |
| graded_at | TIMESTAMP | NULL jika masih ada esai blm dikoreksi |

---

## 3. Desain API Endpoint

### 3.1 Endpoint Guru (`/api/v1/teacher`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET/POST` | `/question-banks` | List & buat bank soal |
| `GET/POST` | `/question-banks/{bank}/questions` | List & tambah soal |
| `PUT/DELETE` | `/questions/{id}` | Edit & hapus soal |
| `POST` | `/question-banks/{bank}/import` | Import soal via Excel |
| `GET/POST` | `/exams` | List & buat paket ujian |
| `PUT` | `/exams/{id}` | Edit paket ujian |
| `POST` | `/exams/{id}/publish` | Publish (draft → published) |
| `GET` | `/exams/{id}/sessions` | **Monitoring** sesi aktif (WebSocket) |
| `GET` | `/exams/{id}/results` | Rekap hasil per kelas |
| `GET` | `/exams/{id}/results/export` | Download Excel |
| `GET` | `/exams/{id}/grade` | List jawaban esai (filter: per soal / per siswa) |
| `POST` | `/exams/{id}/grade` | Simpan koreksi esai: `{answers: [{id, score, notes}]}` |

### 3.2 Endpoint Siswa (`/api/v1/student`)

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/exams` | List ujian: aktif, terjadwal, selesai |
| `GET` | `/exams/upcoming` | Ujian terjadwal saja |
| `GET` | `/exams/{id}` | Detail ujian (sebelum mulai) |
| `POST` | `/exams/{id}/start` | **Mulai ujian** → dapat `session_id`, `token`, daftar soal (acak) |
| `GET` | `/sessions/{id}` | Resume sesi (progress, sisa waktu) |
| `POST` | `/sessions/{id}/answer` | Simpan jawaban `{question_id, answer_data, flagged}` |
| `POST` | `/sessions/{id}/heartbeat` | Keep-alive + report tab_switch_count |
| `POST` | `/sessions/{id}/submit` | **Kumpul** → auto-score objektif, buat result |
| `GET` | `/exams/{id}/result` | Lihat hasil (nilai, detail per soal, tp_scores) |

### 3.3 Contoh Response Kunci

**POST `/student/exams/{id}/start` — Mulai Ujian:**

```json
{
  "session_id": "uuid-1234", "token": "sess_abc123",
  "exam": {"name": "STS B.Indonesia", "duration_minutes": 90, "total_questions": 20},
  "time": {"started_at": "...", "ends_at": "...", "remaining_seconds": 5400},
  "questions": [{
    "id": "q1", "order": 5, "type": "pg", "bobot": 2, "tp_code": "TP-01",
    "question_text": "<p>Bacalah teks...</p>",
    "options": [{"label":"A","text":"..."}, {"label":"B","text":"..."}, ...]
  }],
  "progress": {"answered": [], "flagged": []}
}
```

**POST `/student/exams/sessions/{id}/submit` — Submit:**

```json
{
  "message": "Ujian berhasil dikumpulkan",
  "result": {
    "nilai_akhir": 85.0, "status": "partial",
    "total": 20, "correct": 15, "incorrect": 3, "pending_essay": 2,
    "tp_scores": {
      "TP-01": {"total": 10, "achieved": 9, "percent": 90},
      "TP-02": {"total": 15, "achieved": 12, "percent": 80}
    },
    "note": "2 soal esai menunggu koreksi guru"
  }
}
```

---

## 4. Alur Data Lengkap: Soal → Ujian → Penilaian → Rapor

### 4.1 Timeline

```
FASE 1 — PERSIAPAN (GURU)
───────────────────────────
[T-7 hari] Guru buat soal → questions (dengan tp_id)
[T-3 hari] Guru buat paket ujian → exams + exam_questions + exam_classes
[T-1 hari] Guru publish → exams.status = 'published'
           → Ujian muncul di portal siswa (GET /student/exams)

FASE 2 — PELAKSANAAN (SISWA)
─────────────────────────────
[T+0 08:00] Siswa klik "Mulai" → POST /start
            → exam_sessions dibuat (token, started_at, device_fp)
            → Soal diacak (jika settings.randomize=true) + opsi diacak
            → Return: daftar soal TANPA kunci jawaban

[T+0 08:00–09:30] Siswa menjawab:
            → POST /answer tiap pindah soal (auto-save)
            → PG/BS/Jodoh → auto-score: is_correct = true/false
            → Esai → is_correct = NULL (pending)

[T+0 08:45] Siswa submit lebih awal → POST /submit
            → session.status = 'submitted'
            → Hitung total_score (soal objektif) + max_score
            → Hitung tp_scores JSONB (agregat per TP)
            → Buat exam_result (nilai_akhir, tp_scores, status='partial')

[T+0 09:30] Siswa yang belum submit → auto-submit via cron job
            → session.status = 'timed_out'

FASE 3 — KOREKSI MANUAL (GURU)
───────────────────────────────
[T+1 hari] Guru koreksi esai → POST /teacher/exams/{id}/grade
           → Update answer.score_achieved + is_correct
           → Recalculate exam_result.total_score & tp_scores
           → Jika semua esai terkoreksi → status = 'graded'

FASE 4 — INTEGRASI NILAI & RAPOR
─────────────────────────────────
[Auto] ExamGraded event → UpdateNilaiPerTP listener:
       → Untuk setiap TP di tp_scores:
           Nilai.upsert({
             student_id, mapel_id, tp_id, semester_id,
             nilai: tp_score.percent,  // 0-100
             sumber: 'ujian',
             exam_id
           })
       → Nilai ini TERSEDIA di modul Penilaian
       → Saat wali kelas validasi rapor → nilai ujian SUDAH TERMASUK

FASE 5 — TAMPILAN
─────────────────
Siswa: GET /student/exams/{id}/result → nilai + detail per soal + tp_scores
Siswa/Ortu: GET /penilaian/rapor → nilai per TP termasuk kontribusi ujian
```

### 4.2 Event-Driven Integration (Laravel)

```php
// Event: app/Events/ExamGraded.php
class ExamGraded {
    public function __construct(
        public ExamResult $result,
        public array $tpScores  // dari tp_scores JSONB
    ) {}
}

// Listener: app/Listeners/UpdateNilaiPerTP.php
class UpdateNilaiPerTP {
    public function handle(ExamGraded $event): void {
        foreach ($event->tpScores as $tpCode => $score) {
            $tp = TujuanPembelajaran::where('code', $tpCode)->firstOrFail();
            
            Nilai::updateOrCreate(
                [
                    'student_id'  => $event->result->student_id,
                    'mapel_id'    => $event->result->exam->mapel_id,
                    'tp_id'       => $tp->id,
                    'semester_id' => activeSemester()->id,
                    'sumber'      => 'ujian',
                    'sumber_id'   => $event->result->id,
                ],
                [
                    'nilai'       => $score['percent'],  // 0-100
                    'deskripsi'   => "Ujian: {$event->result->exam->name}",
                ]
            );
        }
    }
}
```

### 4.3 Flow Visual

```
Bank Soal                 Paket Ujian               CBT Siswa
─────────                ────────────              ──────────
questions                exams                     exam_sessions
  │                        │                         │
  │ tp_id=TP-01            │ settings.randomize=true │ token="abc123"
  │ bobot=2                │ security.fullscreen=true│ started_at
  │ type=pg                │                         │
  │                        │                         │
  └──── exam_questions ────┘                         │
           │                                         │
           │  question_id=...                        │
           │  order_number=5                         │
           │                                         │
           └───────────► exam_answers ◄──────────────┘
                              │
                              │ answer_data={"selected":"B"}
                              │ is_correct=true (auto)
                              │ score_achieved=2
                              │
                        exam_results
                              │
                              │ nilai_akhir=85
                              │ tp_scores={"TP-01":{"percent":90},...}
                              │
                              ▼
                    ┌────────────────────┐
                    │ Event: ExamGraded  │
                    │ Listener:           │
                    │ UpdateNilaiPerTP   │
                    └────────┬───────────┘
                             │
                             ▼
                    ┌────────────────────┐
                    │ Modul Nilai & Rapor │
                    │ Nilai per TP        │
                    │ → tampil di rapor   │
                    └────────────────────┘
```

---

## Ringkasan

| Komponen | Detail |
|---|---|
| **Tipe Soal** | 7 tipe (PG, PGK, BS, Jodoh, Isian, Esai, Audio) + media embed |
| **Paket Ujian** | Wizard 4 langkah, dukung seleksi manual & acak, komposisi TP & level kognitif |
| **Keamanan** | 8 mekanisme (acak soal/opsi, fullscreen, deteksi tab, timer server, auto-submit, device limit, blokir copy-paste, IP logging) |
| **Koreksi** | Otomatis: PG/BS/Jodoh (exact), Isian (fuzzy Levenshtein). Manual: Esai (rubrik, shortcut keyboard) |
| **CBT Siswa** | Fullscreen, navigasi grid warna, auto-save, flag ragu, konfirmasi, resume |
| **Database** | 8 tabel: question_banks, questions, exams, exam_questions, exam_classes, exam_sessions, exam_answers, exam_results |
| **API** | 12 endpoint guru + 9 endpoint siswa = 21 endpoint REST |
| **Integrasi Rapor** | `ExamGraded` event → `UpdateNilaiPerTP` listener → auto upsert ke tabel `nilai` — nilai ujian langsung tersedia di rapor |
