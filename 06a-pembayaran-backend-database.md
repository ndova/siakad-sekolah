# Modul Pembayaran SIAKAD — Bagian 1: Database & Backend Bendahara

> **Stack**: Laravel 11 + PostgreSQL + Redis | **Role**: Bendahara/Keuangan

---

## 1. Arsitektur Modul

```
fee_types ──▶ fee_type_targets (jenjang/kelas/jurusan yg dikenai)
    │
    ▼
invoices ──▶ invoice_items (rincian per jenis biaya)
    │
    ▼
payments ◀── payment_methods
    │
    ▼
payment_logs  (audit trail)
```

**Prinsip**: Single source of truth dari backend, no-delete (hanya void), semua perubahan tercatat di `payment_logs`.

---

## 2. Desain Tabel Database

### 2.1 `fee_types` — Master Jenis Biaya

```sql
CREATE TABLE fee_types (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    school_id       UUID NOT NULL REFERENCES schools(id),
    code            VARCHAR(30)  NOT NULL,       -- SPP_BULANAN, UJIAN_SAS, SERAGAM
    name            VARCHAR(150) NOT NULL,       -- "SPP Bulanan"
    description     TEXT,
    category        VARCHAR(20)  NOT NULL DEFAULT 'rutin',  -- 'rutin' | 'tidak_rutin'
    nominal         DECIMAL(12,2) NOT NULL DEFAULT 0,
    billing_period  VARCHAR(10),                 -- 'monthly','semester','yearly', null
    is_active       BOOLEAN NOT NULL DEFAULT true,
    created_at      TIMESTAMP DEFAULT NOW(),
    updated_at      TIMESTAMP DEFAULT NOW(),
    UNIQUE(school_id, code)
);
```

### 2.2 `fee_type_targets` — Sasaran Penerapan Biaya

```sql
CREATE TABLE fee_type_targets (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    fee_type_id     UUID NOT NULL REFERENCES fee_types(id) ON DELETE CASCADE,
    school_id       UUID NOT NULL REFERENCES schools(id),
    target_level    VARCHAR(10) DEFAULT 'all',   -- 'all','jenjang','tingkat','jurusan'
    jenjang         VARCHAR(10),                  -- SMP, SMA, SMK
    tingkat         SMALLINT,                     -- 7-12
    jurusan_id      UUID REFERENCES jurusan(id),
    nominal_override DECIMAL(12,2),               -- null = pakai nominal fee_types
    created_at      TIMESTAMP DEFAULT NOW(),
    UNIQUE(fee_type_id, target_level, jenjang, tingkat, jurusan_id)
);
```

Contoh: SPP SMP nominal 250rb, SPP SMA 300rb → 2 row di `fee_type_targets`.

### 2.3 `invoices` — Tagihan

```sql
CREATE TABLE invoices (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    school_id       UUID NOT NULL REFERENCES schools(id),
    invoice_number  VARCHAR(50) NOT NULL,   -- INV-2025-001-0001
    student_id      UUID NOT NULL REFERENCES students(id),
    academic_year_id UUID NOT NULL REFERENCES academic_years(id),
    semester        VARCHAR(2) NOT NULL DEFAULT '1',
    batch_id        UUID,                    -- tracing batch generate
    status          VARCHAR(20) NOT NULL DEFAULT 'unpaid',
        -- 'unpaid','partial','paid','overdue','void'
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount        DECIMAL(12,2) NOT NULL DEFAULT 0,
    total           DECIMAL(12,2) NOT NULL DEFAULT 0,
    due_date        DATE,
    paid_at         TIMESTAMP,
    voided_at       TIMESTAMP,
    void_reason     TEXT,
    notes           TEXT,
    created_by      UUID REFERENCES users(id),
    created_at      TIMESTAMP DEFAULT NOW(),
    updated_at      TIMESTAMP DEFAULT NOW(),
    UNIQUE(school_id, invoice_number)
);
CREATE INDEX idx_invoices_student ON invoices(student_id, academic_year_id);
CREATE INDEX idx_invoices_status  ON invoices(status, due_date);
```

### 2.4 `invoice_items` — Rincian Tagihan

```sql
CREATE TABLE invoice_items (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    invoice_id      UUID NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    fee_type_id     UUID NOT NULL REFERENCES fee_types(id),
    fee_name        VARCHAR(150) NOT NULL,    -- snapshot nama
    description     TEXT,                      -- "Bulan Januari 2025"
    quantity        SMALLINT NOT NULL DEFAULT 1,
    unit_price      DECIMAL(12,2) NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL,
    period_month    SMALLINT,                  -- 1-12 (utk SPP bulanan)
    period_year     SMALLINT,
    created_at      TIMESTAMP DEFAULT NOW()
);
```

### 2.5 `invoice_batches` — Batch Generate

```sql
CREATE TABLE invoice_batches (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    school_id       UUID NOT NULL REFERENCES schools(id),
    batch_number    VARCHAR(50) NOT NULL,     -- BATCH-SPP-JAN2025
    description     VARCHAR(255),
    academic_year_id UUID NOT NULL REFERENCES academic_years(id),
    jenjang         VARCHAR(10),
    tingkat         SMALLINT,
    jurusan_id      UUID,
    total_invoices  INT NOT NULL DEFAULT 0,
    total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    status          VARCHAR(20) NOT NULL DEFAULT 'draft',  -- 'draft','active','void'
    created_by      UUID REFERENCES users(id),
    created_at      TIMESTAMP DEFAULT NOW()
);
```

### 2.6 `payment_methods` — Metode Pembayaran

```sql
CREATE TABLE payment_methods (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    school_id       UUID NOT NULL REFERENCES schools(id),
    code            VARCHAR(30) NOT NULL,     -- CASH, TRANSFER_BCA, QRIS, MIDTRANS
    name            VARCHAR(100) NOT NULL,
    type            VARCHAR(20) NOT NULL DEFAULT 'offline', -- 'offline','online'
    account_number  VARCHAR(50),
    account_name    VARCHAR(100),
    bank_name       VARCHAR(50),
    is_active       BOOLEAN NOT NULL DEFAULT true,
    instructions    TEXT,
    created_at      TIMESTAMP DEFAULT NOW()
);
```

### 2.7 `payments` — Transaksi Pembayaran

```sql
CREATE TABLE payments (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    school_id       UUID NOT NULL REFERENCES schools(id),
    payment_number  VARCHAR(50) NOT NULL,     -- PAY-2025-0001
    invoice_id      UUID NOT NULL REFERENCES invoices(id),
    student_id      UUID NOT NULL REFERENCES students(id),
    paid_by         VARCHAR(100),              -- nama pembayar
    payment_method_id UUID REFERENCES payment_methods(id),
    payment_channel VARCHAR(30) DEFAULT 'backend',  -- 'backend','portal','gateway'
    amount          DECIMAL(12,2) NOT NULL,
    admin_fee       DECIMAL(12,2) DEFAULT 0,
    gateway_ref     VARCHAR(100),              -- transaction_id Midtrans
    gateway_status  VARCHAR(30),               -- pending, settlement, expire
    proof_file      VARCHAR(255),              -- path bukti transfer
    status          VARCHAR(20) NOT NULL DEFAULT 'pending',
        -- 'pending','verified','rejected','void'
    verified_by     UUID REFERENCES users(id),
    verified_at     TIMESTAMP,
    reject_reason   TEXT,
    payment_date    DATE NOT NULL,
    paid_at         TIMESTAMP DEFAULT NOW(),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT NOW(),
    updated_at      TIMESTAMP DEFAULT NOW(),
    UNIQUE(school_id, payment_number)
);
CREATE INDEX idx_payments_invoice ON payments(invoice_id);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_payments_status  ON payments(status, payment_date);
```

### 2.8 `payment_logs` — Audit Trail

```sql
CREATE TABLE payment_logs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    payment_id      UUID NOT NULL REFERENCES payments(id) ON DELETE CASCADE,
    actor_id        UUID REFERENCES users(id),
    action          VARCHAR(30) NOT NULL,  -- created, verified, rejected, voided
    old_status      VARCHAR(20),
    new_status      VARCHAR(20),
    notes           TEXT,
    metadata        JSONB,
    created_at      TIMESTAMP DEFAULT NOW()
);
```

### 2.9 Relasi Ringkas

```
fee_types 1──M fee_type_targets
fee_types 1──M invoice_items
invoices  1──M invoice_items
invoices  1──M payments
payments  M──1 payment_methods
payments  1──M payment_logs
students  1──M invoices
students  1──M payments
```

---

## 3. Panel Bendahara — Menu & Fitur

### Sidebar Menu

```
📊 Dashboard Keuangan
📋 Master Biaya
   ├── Daftar Jenis Biaya
   ├── Tambah/Edit Biaya + Target
📄 Tagihan
   ├── Generate Tagihan (Wizard 4 langkah)
   ├── Daftar Semua Tagihan
   └── Detail Tagihan per Siswa
💰 Pembayaran
   ├── Input Pembayaran Manual
   ├── Verifikasi Pembayaran
   └── Riwayat Pembayaran
📊 Laporan
   ├── Rekap per Periode
   ├── Laporan Tunggakan
   ├── Pemasukan per Jenis Biaya
   └── Arus Kas Bulanan
⚙️ Pengaturan (Metode Bayar, VA Number)
```

---

### 3.1 Master Biaya

**Halaman Daftar** — tabel: Kode | Nama | Kategori | Periode | Nominal | Aktif | Target (badge jenjang/kelas).

**Form Tambah/Edit**:
- Kode (unik), Nama, Kategori (Rutin/Tidak Rutin), Periode Tagihan, Nominal
- **Target Penerapan** (dynamic list):
  - Pilih level: All / Per Jenjang / Per Tingkat / Per Jurusan
  - Bisa tambah multiple target dengan nominal override per baris
  - Contoh: SPP → SMP 250rb, SMA 300rb, SMK 300rb

---

### 3.2 Generate Tagihan (Wizard 4 Langkah)

**Langkah 1 — Scope**: Pilih Tahun Ajaran + Semester → Filter Jenjang/Tingkat/Jurusan/Rombel → Tampil jumlah siswa (misal 234) → Pilih semua/manual.

**Langkah 2 — Jenis Biaya**: Checklist jenis biaya yang akan ditagih.
- Untuk **Rutin/SPP Bulanan**: pilih rentang bulan (Jan–Jun 2025), opsi "Pisah per bulan" (6 invoice terpisah) atau "Gabung 1 invoice" (1 invoice + 6 items).
- Untuk **Tidak Rutin**: cukup checklist, kuantitas, catatan.
- **Estimasi total** ditampilkan real-time: `234 siswa × Rp 1.600.000 = Rp 374.400.000`.

**Langkah 3 — Konfigurasi**: Jatuh tempo (N hari setelah generate), diskon (%), catatan umum, mode: Draft atau Langsung Publish.

**Langkah 4 — Hasil**: Ringkasan batch, jumlah invoice, total nominal, tombol [Review Draft] / [Publish Semua].

---

### 3.3 Input Pembayaran Manual

Form satu halaman:
1. **Cari Siswa** → autocomplete by NISN/Nama → tampil daftar tagihan aktif
2. **Pilih Tagihan** → tampil sisa yang harus dibayar
3. **Jumlah Bayar** → validasi ≤ sisa tagihan
4. **Metode Bayar**: Tunai / Transfer (pilih bank) / QRIS
5. **Tanggal Bayar** (date picker)
6. **Upload Bukti Transfer** (jika transfer)
7. **Alokasi FIFO Otomatis**: sistem otomatis melunasi item paling awal dulu (SPP Jan → Feb → Mar ...) — atau mode manual pilih item.

Setelah simpan → `payment` status `verified` langsung (karena input bendahara sendiri), `invoice` di-update (partial/lunas).

---

### 3.4 Verifikasi Pembayaran

**Untuk pembayaran dari portal orang tua** (channel=`portal`):

Halaman daftar antrian verifikasi:

```
┌──────────┬──────────────┬──────────┬──────────┬──────────┬──────────┐
│ No. Bayar│ Siswa/Kelas  │ Jumlah   │ Metode   │ Tgl Bayar│ Bukti    │
├──────────┼──────────────┼──────────┼──────────┼──────────┼──────────┤
│ PAY-0045 │ Budi S (7A)  │ 500.000  │ Transfer │ 22/05/25 │ 👁️[IMG] │
│ PAY-0046 │ Citra D (8B) │ 350.000  │ QRIS     │ 22/05/25 │ -        │
└──────────┴──────────────┴──────────┴──────────┴──────────┴──────────┘
```

Klik baris → Modal/Drawer detail:
- Info pembayaran lengkap + bukti transfer (lightbox)
- Cross-check: total bayar vs sisa tagihan, rekening tujuan vs bukti
- Tombol: **[✅ Verifikasi]** — update status `verified`, update invoice `paid_at` jika lunas
- Tombol: **[❌ Tolak]** — isi alasan penolakan, status `rejected`, kirim notifikasi ke orang tua
- Audit otomatis tercatat di `payment_logs`

---

### 3.5 Laporan Keuangan

#### 3.5.1 Rekap Pembayaran per Periode

```
┌─────────────────────────────────────────────────────────────────┐
│  Filter: Tahun Ajaran [2025/2026 ▼]  Semester [Ganjil ▼]       │
│          Bulan [Semua ▼]                [Tampilkan]              │
│                                                                  │
│  ┌────────────────────────┬──────────┬──────────┬──────────┐    │
│  │ Kategori               │ Tertagih │ Terbayar │ % Lunas  │    │
│  ├────────────────────────┼──────────┼──────────┼──────────┤    │
│  │ SPP Bulanan            │58.500.000│52.250.000│  89.3%   │    │
│  │ Ujian Sumatif          │23.400.000│21.060.000│  90.0%   │    │
│  │ Uang Kegiatan          │17.550.000│14.040.000│  80.0%   │    │
│  ├────────────────────────┼──────────┼──────────┼──────────┤    │
│  │ TOTAL                  │99.450.000│87.350.000│  87.8%   │    │
│  └────────────────────────┴──────────┴──────────┴──────────┘    │
│                                                                  │
│  [Export Excel]  [Export PDF]                                    │
└─────────────────────────────────────────────────────────────────┘
```

#### 3.5.2 Laporan Tunggakan

- Tabel: NISN | Nama | Kelas | Total Tagihan | Terbayar | Tunggakan | Jatuh Tempo | Hari Menunggak
- Filter: per kelas, per jenis biaya, threshold (tunggakan > N hari)
- Highlight merah untuk tunggakan > 30 hari
- Tombol **[Kirim Notifikasi Massal]** → FCM/email ke orang tua yang menunggak
- **Aging Report**: 0-30 hari | 31-60 | 61-90 | >90 hari (seperti AR aging)

#### 3.5.3 Pemasukan per Periode

- Grafik batang: pemasukan per bulan
- Breakdown per metode bayar (tunai vs transfer vs online)
- Tren: bandingkan semester ini vs semester lalu
- **Filter drill-down**: klik bulan → tampil daftar pembayaran di bulan itu

#### 3.5.4 Arus Kas Bulanan

- **Masuk**: total pembayaran terverifikasi
- **Ekspektasi**: total tagihan yang jatuh tempo di bulan tersebut (untuk banding)
- **Collection rate** = Masuk / Ekspektasi × 100%

---

## 4. Integrasi Gateway Pembayaran (Desain)

### 4.1 Callback Midtrans (contoh)

```
POST /api/payments/gateway/callback
Headers: X-Signature (HMAC SHA512 verifikasi)

Body:
{
  "transaction_id": "MDT-xxx",
  "order_id": "INV-2025-001-0001",   // invoice_number
  "transaction_status": "settlement",
  "payment_type": "bank_transfer",
  "gross_amount": "500000",
  "fraud_status": "accept"
}
```

Logic:
1. Verifikasi signature
2. Cari invoice by `order_id`
3. Cek `transaction_status`: `settlement` → buat Payment auto-verified; `expire`/`cancel` → abaikan
4. Update invoice status (partial/lunas)
5. Insert `payment_logs` + kirim notifikasi ke orang tua

### 4.2 Auto-generate SPP Bulanan

Scheduled Task (Laravel Scheduler) — tiap tanggal 1:
```php
// app/Console/Commands/GenerateMonthlySPP.php
// 1. Ambil semua siswa aktif
// 2. Untuk tiap siswa, cek fee_type SPP_BULANAN (via fee_type_targets)
// 3. Generate 1 invoice dengan 1 invoice_item (bulan berjalan)
// 4. Set due_date = tanggal 10 bulan berjalan
// 5. Kirim notifikasi ke orang tua
```

Atau **manual trigger** oleh bendahara lewat wizard untuk kontrol lebih.

### 4.3 Notifikasi Otomatis

| Trigger | Notifikasi |
|---|---|
| Invoice created | "Tagihan baru: SPP Januari 2025 sebesar Rp 250.000" |
| 3 hari sebelum due_date | "Pengingat: tagihan jatuh tempo 15 Jan" |
| Due date lewat | "Tagihan SPP menunggak, segera lakukan pembayaran" |
| Payment verified | "Pembayaran Rp 500.000 telah diverifikasi" |
| Payment rejected | "Pembayaran ditolak: bukti tidak sesuai. Silakan upload ulang" |

Channel: Push (FCM) + opsional Email.

---

**Lanjut ke Bagian 2**: `06b-pembayaran-portal-api-alur.md` — Portal siswa/ortu, API endpoints, dan alur data lengkap.
