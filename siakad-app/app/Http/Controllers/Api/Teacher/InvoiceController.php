<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use App\Models\FeeTypeTarget;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Daftar jenis biaya
     */
    public function feeTypes(Request $request): JsonResponse
    {
        $user = $request->user();
        $feeTypes = FeeType::withCount('invoiceItems')
            ->where('school_id', $user->school_id)
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $feeTypes,
        ]);
    }

    /**
     * Tambah jenis biaya baru
     */
    public function createFeeType(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'code' => ['required','string','max:50', Rule::unique('fee_types','code')],
            'name' => 'required|string|max:255',
            'category' => ['required', Rule::in(['spp', 'daftar_ulang', 'ujian', 'kegiatan', 'seragam', 'lainnya'])],
            'nominal' => 'required|numeric|min:0',
            'billing_period' => ['required', Rule::in(['sekali', 'bulanan', 'semesteran', 'tahunan'])],
            'is_active' => 'boolean',
        ]);

        $feeType = FeeType::create([
            'school_id' => $user->school_id,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'nominal' => $validated['nominal'],
            'billing_period' => $validated['billing_period'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jenis biaya berhasil dibuat',
            'data' => $feeType,
        ], 201);
    }

    /**
     * Daftar tagihan/invoice
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $invoices = Invoice::with([
            'student:id,nama_lengkap,nis,class_id',
            'student.class:id,code,tingkat',
            'items:id,invoice_id,fee_name,nominal',
        ])
            ->withSum('payments as paid_amount', DB::raw("CASE WHEN status = 'verified' THEN amount ELSE 0 END"))
            ->where('school_id', $user->school_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->class_id, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('class_id', $request->class_id)))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('invoice_number', 'like', "%{$request->search}%")
                        ->orWhereHas('student', fn($ssq) => $ssq->where('nama_lengkap', 'like', "%{$request->search}%"));
                });
            })
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $invoices->through(function ($inv) {
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'student' => [
                        'id' => $inv->student?->id,
                        'nama' => $inv->student?->nama_lengkap,
                        'nis' => $inv->student?->nis,
                        'kelas' => $inv->student?->class?->code,
                    ],
                    'items' => $inv->items->map(fn($i) => [
                        'fee_name' => $i->fee_name,
                        'amount' => (float) ($i->nominal ?? 0),
                    ]),
                    'subtotal' => (float) $inv->subtotal,
                    'discount' => (float) $inv->discount,
                    'total' => (float) $inv->total,
                    'paid_amount' => (float) ($inv->paid_amount ?? 0),
                    'remaining' => max(0, (float) $inv->total - (float) ($inv->paid_amount ?? 0)),
                    'status' => $inv->status,
                    'due_date' => $inv->due_date?->format('Y-m-d'),
                    'paid_at' => $inv->paid_at?->toISOString(),
                    'notes' => $inv->notes,
                ];
            }),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Generate tagihan untuk siswa berdasarkan jenis biaya
     */
    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'fee_type_ids' => 'required|array|min:1',
            'fee_type_ids.*' => 'exists:fee_types,id',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $schoolId = $user->school_id;

        // Tentukan target siswa
        $studentsQuery = Student::where('school_id', $schoolId)->where('status', 'aktif');
        if (!empty($validated['student_ids'])) {
            $studentsQuery->whereIn('id', $validated['student_ids']);
        } elseif (!empty($validated['class_ids'])) {
            $studentsQuery->whereIn('class_id', $validated['class_ids']);
        }

        $students = $studentsQuery->get();
        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada siswa yang sesuai'], 400);
        }

        $feeTypes = FeeType::whereIn('id', $validated['fee_type_ids'])
            ->where('is_active', true)
            ->where('school_id', $schoolId)
            ->get();

        if ($feeTypes->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Jenis biaya tidak ditemukan atau tidak aktif'], 400);
        }

        $activeSemester = Semester::where('is_active', true)->first();
        $semesterLabel = $activeSemester ? $activeSemester->semester_number : '1';

        DB::beginTransaction();
        try {
            $generated = 0;

            foreach ($students as $student) {
                // Cek apakah sudah ada tagihan dengan fee types yang sama di semester ini (hindari duplikasi)
                $totalNominal = 0;
                $itemsData = [];

                foreach ($feeTypes as $ft) {
                    $totalNominal += $ft->nominal;
                    $itemsData[] = [
                        'fee_type_id' => $ft->id,
                        'fee_name' => $ft->name,
                        'description' => "{$ft->name} - {$ft->category}",
                        'quantity' => 1,
                        'unit_price' => $ft->nominal,
                        'subtotal' => $ft->nominal,
                        'period_month' => now()->month,
                        'period_year' => now()->year,
                    ];
                }

                $invoiceNumber = 'INV-' . date('ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $discount = 0;
                $total = $totalNominal - $discount;

                $invoice = Invoice::create([
                    'school_id' => $schoolId,
                    'invoice_number' => $invoiceNumber,
                    'student_id' => $student->id,
                    'academic_year_id' => $activeSemester?->academic_year_id,
                    'semester' => $semesterLabel,
                    'batch_id' => 'BATCH-' . date('ymd') . '-' . uniqid(),
                    'status' => 'unpaid',
                    'subtotal' => $totalNominal,
                    'discount' => $discount,
                    'total' => $total,
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $user->id,
                ]);

                foreach ($itemsData as $item) {
                    InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
                }

                $generated++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$generated tagihan berhasil dibuat untuk {$students->count()} siswa",
                'generated_count' => $generated,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal generate tagihan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Detail tagihan
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice->load([
            'student:id,nama_lengkap,nis,nisn,class_id,alamat',
            'student.class:id,code,tingkat',
            'items',
            'payments' => fn($q) => $q->orderBy('created_at', 'desc'),
            'creator:id,name',
        ]);

        $paidAmount = (float) $invoice->payments()->where('status', 'verified')->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'student' => [
                    'id' => $invoice->student?->id,
                    'nama' => $invoice->student?->nama_lengkap,
                    'nis' => $invoice->student?->nis,
                    'nisn' => $invoice->student?->nisn,
                    'kelas' => $invoice->student?->class?->code,
                    'tingkat' => $invoice->student?->class?->tingkat,
                    'alamat' => $invoice->student?->alamat,
                ],
                'items' => $invoice->items->map(fn($i) => [
                    'id' => $i->id,
                    'fee_name' => $i->fee_name,
                    'description' => $i->description,
                    'quantity' => $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'subtotal' => (float) $i->subtotal,
                ]),
                'subtotal' => (float) $invoice->subtotal,
                'discount' => (float) $invoice->discount,
                'total' => (float) $invoice->total,
                'paid' => $paidAmount,
                'remaining' => max(0, (float) $invoice->total - $paidAmount),
                'status' => $invoice->status,
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'paid_at' => $invoice->paid_at?->toISOString(),
                'notes' => $invoice->notes,
                'creator' => $invoice->creator?->name,
                'payments' => $invoice->payments->map(fn($p) => [
                    'id' => $p->id,
                    'payment_number' => $p->payment_number,
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                    'payment_method' => $p->payment_channel,
                    'payment_date' => $p->payment_date?->format('Y-m-d'),
                    'verified_at' => $p->verified_at?->toISOString(),
                ]),
            ],
        ]);
    }

    /**
     * Void / batalkan tagihan
     */
    public function void(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($invoice->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Tagihan sudah di-void'], 400);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Tagihan yang sudah lunas tidak dapat di-void'], 400);
        }

        $invoice->update([
            'status' => 'voided',
            'voided_at' => now(),
            'void_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dibatalkan',
        ]);
    }
}
