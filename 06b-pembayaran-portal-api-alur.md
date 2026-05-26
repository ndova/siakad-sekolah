# Modul Pembayaran SIAKAD — Bagian 2: Portal, API & Alur Data

> **Lanjutan dari**: `06a-pembayaran-backend-database.md`  
> **Target**: Portal Siswa & Orang Tua + REST API + Alur Data Lengkap

---

## 1. Fitur Portal Siswa & Orang Tua

### 1.1 Halaman: Daftar Tagihan

```
┌─────────────────────────────────────────────────────────────────┐
│  TAGIHAN SAYA                                                    │
│                                                                  │
│  Status Filter: [Semua] [Belum Lunas] [Lunas] [Menunggak]       │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ 🔴 MENUNGGAK                                     Rp 1.1JT │   │
│  │ INV-2025-001-0002 — Jatuh tempo: 15 Jan 2025             │   │
│  │ SPP Semester Genap (Jan–Jun) + Ujian Sumatif             │   │
│  │ Status: Sebagian (Rp 500.000 / Rp 1.600.000)             │   │
│  │                                    [💳 Bayar Sekarang]    │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ ⚪ BELUM DIBAYAR                                    Rp 350K│   │
│  │ INV-2025-001-0045 — Jatuh tempo: 20 Mei 2025             │   │
│  │ Seragam Sekolah                                           │   │
│  │ Status: Belum Dibayar                                     │   │
│  │                                    [💳 Bayar Sekarang]    │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ ✅ LUNAS                                           Rp 250K│   │
│  │ INV-2024-002-0120 — Dibayar: 10 Des 2024                 │   │
│  │ Uang Kegiatan Semester Ganjil                             │   │
│  │                                    [👁️ Lihat Detail]     │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

Fitur:
- **Kartu tagihan** dengan warna status: 🔴 menunggak, ⚪ belum dibayar, 🟡 sebagian, ✅ lunas
- Showing sisa/nominal dan jatuh tempo
- Tombol **Bayar Sekarang** hanya untuk tagihan non-lunas
- Pull-to-refresh untuk update status real-time

### 1.2 Halaman: Detail Tagihan

```
┌─────────────────────────────────────────────────────────────────┐
│  DETAIL TAGIHAN                                                  │
│                                                                  │
│  INV-2025-001-0002                       🔴 MENUNGGAK           │
│  Jatuh tempo: 15 Januari 2025 (128 hari keterlambatan)          │
│                                                                  │
│  ─── RINCIAN ───────────────────────────────────────────────    │
│  │  SPP Januari 2025              Rp 250.000    ✅ Lunas       │
│  │  SPP Februari 2025             Rp 250.000    ✅ Lunas       │
│  │  SPP Maret 2025                Rp 250.000    ❌ Belum       │
│  │  SPP April 2025                Rp 250.000    ❌ Belum       │
│  │  SPP Mei 2025                  Rp 250.000    ❌ Belum       │
│  │  SPP Juni 2025                 Rp 250.000    ❌ Belum       │
│  │  Ujian Sumatif Genap 2025      Rp 100.000    ❌ Belum       │
│  │                                                               │
│  │  Total          Rp 1.600.000                                 │
│  │  Terbayar       Rp   500.000                                 │
│  │  Sisa           Rp 1.100.000                                 │
│                                                                  │
│  ─── METODE PEMBAYARAN ─────────────────────────────────────    │
│  │  ● Transfer Bank BCA 1400123456 a.n. Yayasan Sekolah        │
│  │  ○ Transfer Bank BNI 0800123456 a.n. Yayasan Sekolah        │
│  │  ○ QRIS (scan kode)                                          │
│  │  ○ Bayar Langsung ke Bendahara (Tunai)                       │
│  │                                                               │
│  │  [💳 Bayar via Transfer]  [📋 Salin No. Rekening]            │
│                                                                  │
│  ─── RIWAYAT PEMBAYARAN ────────────────────────────────────    │
│  │  10 Jan 2025 | Rp 500.000 | Transfer BCA | ✅ Terverifikasi  │
│                                                                  │
│  [⬅ Kembali]                                                    │
└─────────────────────────────────────────────────────────────────┘
```

### 1.3 Flow Pembayaran Online (Dummy/Simulasi)

Desain halaman untuk portal — tanpa integrasi gateway sesungguhnya:

```
┌─────────────────────────────────────────────────────────────────┐
│  KONFIRMASI PEMBAYARAN                                           │
│                                                                  │
│  Tagihan     : INV-2025-001-0002                                │
│  Total       : Rp 1.600.000                                     │
│  Sisa        : Rp 1.100.000                                     │
│                                                                  │
│  Jumlah Bayar*  [500.000       ]  (maks Rp 1.100.000)           │
│                                                                  │
│  Metode *      (●) Transfer Bank                                 │
│                ( ) QRIS                                          │
│                ( ) Tunai (ke Bendahara)                         │
│                                                                  │
│  ─── INSTRUKSI ─────────────────────────────────────────────    │
│  │  Silakan transfer ke:                                        │
│  │  Bank BCA 1400123456 a.n. Yayasan Sekolah                    │
│  │  Nominal: Rp 500.000                                         │
│  │                                                               │
│  │  Setelah transfer, upload bukti:                             │
│  │  [Pilih File] bukti-transfer.jpg                             │
│  │                                                               │
│  │  Catatan: [Pembayaran SPP Maret-April_______]               │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ ⚠️ Pembayaran akan diverifikasi bendahara dalam 1×24 jam │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [Batal]                                [Konfirmasi Pembayaran]  │
└─────────────────────────────────────────────────────────────────┘
```

Setelah submit: status pembayaran `pending` — menunggu verifikasi bendahara.

### 1.4 Halaman: Riwayat Pembayaran

```
┌─────────────────────────────────────────────────────────────────┐
│  RIWAYAT PEMBAYARAN                         [📥 Download PDF]    │
│                                                                  │
│  Semester: [Genap 2025 ▼]                                       │
│                                                                  │
│  ┌──────────┬──────────────┬──────────┬──────────┬──────────┐   │
│  │ Tanggal  │ Invoice      │ Jumlah   │ Metode   │ Status   │   │
│  ├──────────┼──────────────┼──────────┼──────────┼──────────┤   │
│  │10/01/25  │ INV-...-0002 │ 500.000  │ BCA Trsf │✅ Verif  │   │
│  │10/12/24  │ INV-...-0120 │ 250.000  │ Tunai    │✅ Verif  │   │
│  │05/08/24  │ INV-...-0089 │1.200.000 │ BCA Trsf │✅ Verif  │   │
│  └──────────┴──────────────┴──────────┴──────────┴──────────┘   │
│                                                                  │
│  Total Pembayaran Semester Ini: Rp 500.000                       │
└─────────────────────────────────────────────────────────────────┘
```

### 1.5 Notifikasi di Portal

Icon 🔔 pada header portal dengan badge jumlah:

| Notifikasi | Aksi |
|---|---|
| "Tagihan baru: SPP Juni 2025 — Rp 250.000" | Klik → buka detail tagihan |
| "Pengingat: tagihan jatuh tempo besok" | Klik → buka tagihan terkait |
| "Pembayaran Rp 500.000 telah diverifikasi ✅" | Klik → buka riwayat |
| "Pembayaran ditolak: bukti tidak sesuai" | Klik → buka detail + upload ulang |

---

## 2. Desain API Endpoint

### 2.1 API untuk Portal Siswa / Orang Tua

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/payments/bills` | Daftar tagihan siswa (filter: status) |
| `GET` | `/api/payments/bills/{invoiceId}` | Detail satu tagihan + items + payment history |
| `GET` | `/api/payments/history` | Riwayat pembayaran (filter: semester, tahun) |
| `POST` | `/api/payments/bills/{invoiceId}/pay` | Submit pembayaran (dummy flow) |
| `GET` | `/api/payments/methods` | Daftar metode pembayaran aktif |
| `GET` | `/api/payments/summary` | Ringkasan: total tagihan, terbayar, tunggakan |
| `GET` | `/api/payments/notifications` | Notifikasi pembayaran user |

#### Response Contoh

**`GET /api/payments/bills`**
```json
{
  "data": [
    {
      "id": "uuid",
      "invoice_number": "INV-2025-001-0002",
      "status": "partial",
      "subtotal": 1600000,
      "total": 1600000,
      "paid_amount": 500000,
      "remaining": 1100000,
      "due_date": "2025-01-15",
      "days_overdue": 128,
      "items": [
        {"fee_name": "SPP Januari 2025", "subtotal": 250000, "is_paid": true},
        {"fee_name": "SPP Februari 2025", "subtotal": 250000, "is_paid": true},
        {"fee_name": "SPP Maret 2025", "subtotal": 250000, "is_paid": false}
      ],
      "created_at": "2025-01-01"
    }
  ],
  "summary": {
    "total_bills": 5600000,
    "total_paid": 3200000,
    "total_unpaid": 2400000,
    "overdue_count": 1
  }
}
```

**`POST /api/payments/bills/{invoiceId}/pay`**
```json
// Request
{
  "amount": 500000,
  "payment_method_id": "uuid-transfer-bca",
  "payment_date": "2025-05-23",
  "proof_file": "<base64 atau multipart upload>",
  "notes": "Pembayaran SPP Maret-April 2025"
}

// Response 201
{
  "message": "Pembayaran berhasil dikirim, menunggu verifikasi bendahara",
  "data": {
    "id": "uuid",
    "payment_number": "PAY-2025-0045",
    "amount": 500000,
    "status": "pending",
    "payment_date": "2025-05-23"
  }
}
```

**Logic Backend `pay` endpoint:**
1. Validasi: `amount` ≤ sisa tagihan, `invoice` status bukan `paid`/`void`
2. Cek `payment_method_id` valid & aktif
3. Upload `proof_file` ke storage (Laravel filesystem)
4. Insert ke `payments` dengan `status = pending`, `payment_channel = portal`
5. Insert `payment_logs` (action=`created`)
6. Kirim notifikasi ke bendahara: "Pembayaran baru dari Budi Santoso, Rp 500.000"

### 2.2 API untuk Backend Bendahara

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/teacher/fee-types` | Daftar master biaya |
| `POST` | `/api/teacher/fee-types` | Tambah jenis biaya |
| `PUT` | `/api/teacher/fee-types/{id}` | Edit jenis biaya + target |
| `DELETE`| `/api/teacher/fee-types/{id}` | Nonaktifkan biaya |
| `GET` | `/api/teacher/invoices` | Daftar semua tagihan (filterable) |
| `GET` | `/api/teacher/invoices/{id}` | Detail tagihan |
| `POST` | `/api/teacher/invoices/generate` | Generate tagihan (batch) |
| `POST` | `/api/teacher/invoices/{id}/void` | Batalkan tagihan |
| `GET` | `/api/teacher/payments` | Daftar pembayaran (filter: status) |
| `POST` | `/api/teacher/payments/manual` | Input pembayaran manual |
| `POST` | `/api/teacher/payments/{id}/verify` | Verifikasi pembayaran |
| `POST` | `/api/teacher/payments/{id}/reject` | Tolak pembayaran |
| `GET` | `/api/teacher/reports/finance` | Laporan keuangan (query params: period, type) |
| `GET` | `/api/teacher/reports/overdue` | Laporan tunggakan |
| `GET` | `/api/teacher/payment-methods` | Kelola metode pembayaran |
| `POST` | `/api/payments/gateway/callback` | Callback payment gateway (public) |

**`GET /api/teacher/reports/finance`** query params:
```
?academic_year_id=uuid
&semester=1
&month=1            // optional, null = all months
&type=summary        // 'summary' | 'detail' | 'cashflow'
```

**Response `summary`:**
```json
{
  "period": "2025/2026 Ganjil",
  "breakdown": [
    {
      "fee_type": "SPP Bulanan",
      "total_billed": 58500000,
      "total_paid": 52250000,
      "collection_rate": 89.3
    }
  ],
  "totals": {
    "billed": 99450000,
    "paid": 87350000,
    "outstanding": 12100000,
    "collection_rate": 87.8
  },
  "monthly": [
    {"month": 1, "billed": 15000000, "paid": 13500000},
    {"month": 2, "billed": 15000000, "paid": 12800000}
  ]
}
```

---

## 3. Alur Data Lengkap

### 3.1 Alur Utama: Tagihan → Bayar → Verifikasi → Lunas

```
┌─────────────────────────────────────────────────────────────────┐
│  FASE 1: PEMBUATAN TAGIHAN (oleh Bendahara)                      │
│                                                                  │
│  Bendahara login → Panel Backend                                 │
│    → Menu "Generate Tagihan"                                     │
│      → Wizard 4 langkah:                                         │
│        1. Pilih scope (siswa, kelas, jenjang)                    │
│        2. Pilih jenis biaya + konfigurasi                        │
│        3. Review + jatuh tempo                                   │
│        4. Konfirmasi → INSERT ke DB                              │
│                                                                  │
│  DB State setelah generate:                                      │
│  ┌─────────────┐    ┌──────────────────┐                         │
│  │invoice_batches│   │    invoices      │                         │
│  │status=active  │──▶│ status=unpaid    │                         │
│  │total=234      │   │ due_date=14 hari │                         │
│  └─────────────┘    └──────┬───────────┘                         │
│                            │                                      │
│                    ┌───────▼───────────┐                          │
│                    │  invoice_items    │                          │
│                    │  (rincian per fee)│                          │
│                    └───────────────────┘                          │
│                                                                  │
│  Event: InvoiceCreated → kirim FCM ke orang tua                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  FASE 2: TAGIHAN MUNCUL DI PORTAL                                │
│                                                                  │
│  Siswa/Orang Tua login → Portal                                  │
│    → Menu "Pembayaran" / "Tagihan"                              │
│      → GET /api/payments/bills                                   │
│        → SELECT invoices WHERE student_id = ?                    │
│          JOIN invoice_items                                      │
│          LEFT JOIN payments (untuk hitung paid_amount)           │
│        → Response: daftar tagihan + status + sisa               │
│                                                                  │
│  Portal menampilkan kartu tagihan dengan warna status:           │
│    🟢 Lunas    🟡 Sebagian    🔴 Menunggak    ⚪ Belum Dibayar   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  FASE 3: ORANG TUA MELAKUKAN PEMBAYARAN                          │
│                                                                  │
│  Orang Tua klik "Bayar Sekarang" pada tagihan                    │
│    → Halaman Konfirmasi Pembayaran                               │
│      → Pilih jumlah bayar, metode, upload bukti                  │
│      → POST /api/payments/bills/{id}/pay                         │
│                                                                  │
│  Backend logic:                                                  │
│    1. Validasi amount ≤ remaining & invoice.status != paid       │
│    2. Upload proof_file → storage/app/payments/bukti-xxx.jpg     │
│    3. INSERT INTO payments (                                      │
│         invoice_id, student_id, amount, payment_method_id,       │
│         payment_channel='portal', status='pending',              │
│         proof_file, payment_date                                 │
│       )                                                          │
│    4. INSERT INTO payment_logs (action='created')                │
│    5. Event PaymentSubmitted → notifikasi ke bendahara           │
│                                                                  │
│  DB State:                                                       │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │ payments: status='pending', amount=500000                │    │
│  │ invoices: status tetap 'unpaid'/'partial' (belum berubah)│    │
│  │ invoice_items: tidak berubah                             │    │
│  └──────────────────────────────────────────────────────────┘    │
│                                                                  │
│  Portal: tampil "Pembayaran dikirim, menunggu verifikasi"        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  FASE 4: BENDAHARA VERIFIKASI                                    │
│                                                                  │
│  Bendahara login → Panel Backend                                 │
│    → Menu "Verifikasi Pembayaran"                                │
│      → GET /api/teacher/payments?status=pending                  │
│      → Lihat daftar antrian pembayaran                            │
│      → Klik detail → lihat bukti transfer (lightbox)             │
│                                                                  │
│  Opsi A: VERIFIKASI ✅                                           │
│    POST /api/teacher/payments/{id}/verify                        │
│    Backend:                                                      │
│      1. UPDATE payments SET status='verified',                   │
│           verified_by=?, verified_at=NOW()                        │
│      2. INSERT payment_logs (action='verified')                  │
│      3. Hitung ulang total terbayar invoice:                     │
│           SELECT SUM(amount) FROM payments                       │
│           WHERE invoice_id=? AND status='verified'               │
│      4. Update invoices:                                         │
│           IF total_paid >= invoice.total:                        │
│             UPDATE invoices SET status='paid', paid_at=NOW()     │
│           ELSE IF total_paid > 0:                                │
│             UPDATE invoices SET status='partial'                 │
│      5. Event PaymentVerified → FCM ke orang tua ✅              │
│      6. Update payment_notifications                             │
│                                                                  │
│  Opsi B: TOLAK ❌                                                │
│    POST /api/teacher/payments/{id}/reject                        │
│    Body: { "reason": "Bukti transfer tidak sesuai" }            │
│    Backend:                                                      │
│      1. UPDATE payments SET status='rejected',                   │
│           reject_reason=?                                        │
│      2. INSERT payment_logs (action='rejected')                  │
│      3. Event PaymentRejected → FCM ke orang tua ❌              │
│      4. Portal orang tua: tampil banner "Pembayaran ditolak"     │
│         + tombol upload ulang                                    │
│                                                                  │
│  DB Final State (jika verified):                                 │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │ payments: status='verified', verified_by=bendahara_id   │    │
│  │ invoices: status='partial'/'paid', paid_at=(jika lunas) │    │
│  │ payment_logs: 2 row (created + verified)                 │    │
│  └──────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  FASE 5: STATUS LUNAS DI PORTAL                                  │
│                                                                  │
│  Setelah diverifikasi:                                           │
│                                                                  │
│  Portal Siswa:                                                   │
│    → Tagihan berubah status: 🟢 LUNAS / 🟡 SEBAGIAN              │
│    → Riwayat pembayaran muncul entry baru ✅                     │
│                                                                  │
│  Portal Orang Tua:                                               │
│    → Sama seperti siswa + notifikasi "Pembayaran diverifikasi"  │
│                                                                  │
│  Dashboard Bendahara:                                            │
│    → Laporan terupdate: collection rate naik                     │
│    → Tunggakan berkurang                                         │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Alur Khusus: Payment Gateway (Auto-Verify)

```
Orang Tua pilih "Bayar via Midtrans" di portal
    → POST /api/payments/bills/{id}/pay (dengan payment_method_id=midtrans)
    → Backend buat payment.status='pending' + dapatkan snap_token dari Midtrans
    → Redirect ke halaman pembayaran Midtrans
    → Orang tua bayar di Midtrans

Midtrans callback:
    → POST /api/payments/gateway/callback
    → Verifikasi signature HMAC SHA512
    → Jika settlement:
        → UPDATE payments SET status='verified', gateway_status='settlement'
        → UPDATE invoices (lunas/sebagian)
        → Kirim notifikasi ke orang tua ✅
    → Jika expire/cancel:
        → UPDATE payments SET status='void', gateway_status='expire'
```

### 3.3 Integrasi dengan Modul Lain

**Dengan Modul Akademik/Rapor**:
- Saat generate rapor, sistem bisa cek status pembayaran
- Opsi konfigurasi: *"Blokir akses rapor jika ada tunggakan"*
- Cek: `SELECT COUNT(*) FROM invoices WHERE student_id=? AND status IN ('unpaid','partial','overdue')`

**Dengan Modul Kenaikan Kelas**:
- Sebelum kenaikan kelas, sistem bisa validasi: *"Siswa harus lunas semua tagihan tahun ajaran aktif"*
- Generate laporan "Siswa dengan tunggakan" untuk rapat kenaikan

---

## 4. Ringkasan Keseluruhan

### 4.1 Tabel

| Tabel | Fungsi |
|---|---|
| `fee_types` | Master jenis biaya (SPP, ujian, seragam, dll) |
| `fee_type_targets` | Mapping biaya ke jenjang/kelas/jurusan tertentu |
| `invoices` | Tagihan per siswa per periode |
| `invoice_items` | Rincian item dalam satu tagihan |
| `invoice_batches` | Referensi batch generate (untuk tracing) |
| `payment_methods` | Metode bayar (tunai, transfer, QRIS, gateway) |
| `payments` | Transaksi pembayaran (dengan status verifikasi) |
| `payment_logs` | Audit trail semua perubahan |

### 4.2 API Endpoint

| Role | Jumlah | Keterangan |
|---|---|---|
| Portal Siswa/Ortu | 7 endpoint | Bills, history, pay, methods, summary, notifications |
| Backend Bendahara | 15 endpoint | Fee types CRUD, invoice management, payment verification, reports |
| Public | 1 endpoint | Gateway callback |

### 4.3 Fitur Kunci

- **Generate tagihan masal** via wizard 4 langkah dengan auto-kalkulasi
- **SPP bulanan otomatis** via Laravel Scheduler atau manual trigger
- **Dual-channel pembayaran**: offline (bendahara input) + online (portal → verifikasi)
- **Alokasi pembayaran FIFO**: sistem otomatis melunasi item tertua lebih dulu
- **Notifikasi real-time** via FCM: tagihan baru, pengingat jatuh tempo, verifikasi
- **Laporan lengkap**: rekap, tunggakan + aging, arus kas, collection rate
- **Audit trail**: semua perubahan tercatat di `payment_logs`, data keuangan tidak dihapus
- **Integrasi gateway**: Midtrans/Xendit callback dengan verifikasi signature
