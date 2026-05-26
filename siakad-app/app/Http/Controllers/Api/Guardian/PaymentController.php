<?php

namespace App\Http\Controllers\Api\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Daftar tagihan semua anak
     */
    public function bills(Request $request): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::with('students')->where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        $studentIds = $guardian->students->pluck('id');

        $invoices = Invoice::with(['items', 'student:id,nama_lengkap,nis,class_id', 'student.class:id,code'])
            ->whereIn('student_id', $studentIds)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
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
                    'items' => $inv->items->map(fn($i) => $i->fee_name),
                    'total' => (float) $inv->total,
                    'paid' => (float) $inv->paid_amount,
                    'remaining' => $inv->remaining,
                    'status' => $inv->status,
                    'due_date' => $inv->due_date?->format('Y-m-d'),
                    'paid_at' => $inv->paid_at?->toISOString(),
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
     * Detail tagihan
     */
    public function billDetail(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::with('students')->where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        // Verifikasi bahwa invoice milik anak guardian ini
        $studentIds = $guardian->students->pluck('id');
        if (!$studentIds->contains($invoice->student_id)) {
            return response()->json(['success' => false, 'message' => 'Tagihan bukan untuk anak Anda'], 403);
        }

        $invoice->load([
            'student:id,nama_lengkap,nis,class_id',
            'student.class:id,code',
            'items',
            'payments' => fn($q) => $q->orderBy('created_at', 'desc'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'student' => [
                    'nama' => $invoice->student?->nama_lengkap,
                    'nis' => $invoice->student?->nis,
                    'kelas' => $invoice->student?->class?->code,
                ],
                'items' => $invoice->items->map(fn($i) => [
                    'fee_name' => $i->fee_name,
                    'description' => $i->description,
                    'quantity' => $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'subtotal' => (float) $i->subtotal,
                ]),
                'subtotal' => (float) $invoice->subtotal,
                'discount' => (float) $invoice->discount,
                'total' => (float) $invoice->total,
                'paid' => (float) $invoice->paid_amount,
                'remaining' => $invoice->remaining,
                'status' => $invoice->status,
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'notes' => $invoice->notes,
                'payments' => $invoice->payments->map(fn($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                    'payment_date' => $p->payment_date?->format('Y-m-d'),
                    'channel' => $p->payment_channel,
                    'verified_at' => $p->verified_at?->toISOString(),
                ]),
            ],
        ]);
    }

    /**
     * Bayar tagihan (submit pembayaran)
     */
    public function pay(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::with('students')->where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        $studentIds = $guardian->students->pluck('id');
        if (!$studentIds->contains($invoice->student_id)) {
            return response()->json(['success' => false, 'message' => 'Tagihan bukan untuk anak Anda'], 403);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Tagihan sudah lunas'], 400);
        }

        if ($invoice->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Tagihan sudah dibatalkan'], 400);
        }

        $remaining = $invoice->remaining;
        $validated = $request->validate([
            'amount' => "required|numeric|min:1|max:{$remaining}",
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_channel' => 'required|string|max:50',
            'proof_file' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $paymentNumber = 'PAY-' . date('ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'school_id' => $invoice->school_id,
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'paid_by' => $user->id,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'payment_channel' => $validated['payment_channel'],
                'amount' => $validated['amount'],
                'admin_fee' => 0,
                'proof_file' => $validated['proof_file'] ?? null,
                'status' => 'pending',
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $validated['notes'] ?? null,
            ]);

            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'submit',
                'note' => 'Pembayaran diajukan oleh orang tua',
                'user_id' => $user->id,
            ]);

            // Update invoice status to partial if not fully paid
            $paidAmount = (float) $invoice->payments()->where('status', 'verified')->sum('amount');
            $newStatus = ($paidAmount + $validated['amount']) >= (float) $invoice->total ? 'paid' : 'partial';
            // Don't set paid until verified
            $invoice->update([
                'status' => $invoice->status === 'unpaid' ? 'partial' : $invoice->status,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diajukan, menunggu verifikasi',
                'data' => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date?->format('Y-m-d'),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Riwayat pembayaran orang tua
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $guardian = Guardian::with('students')->where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['success' => false, 'message' => 'Data orang tua tidak ditemukan'], 404);
        }

        $studentIds = $guardian->students->pluck('id');

        $payments = Payment::with([
            'student:id,nama_lengkap,nis,class_id',
            'student.class:id,code',
            'invoice:id,invoice_number,total',
        ])
            ->whereIn('student_id', $studentIds)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $payments->through(function ($p) {
                return [
                    'id' => $p->id,
                    'payment_number' => $p->payment_number,
                    'student' => [
                        'nama' => $p->student?->nama_lengkap,
                        'kelas' => $p->student?->class?->code,
                    ],
                    'invoice_number' => $p->invoice?->invoice_number,
                    'amount' => (float) $p->amount,
                    'status' => $p->status,
                    'payment_channel' => $p->payment_channel,
                    'payment_date' => $p->payment_date?->format('Y-m-d'),
                    'verified_at' => $p->verified_at?->toISOString(),
                    'reject_reason' => $p->reject_reason,
                ];
            }),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }
}
