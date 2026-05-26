<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentLog;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Daftar pembayaran
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $payments = Payment::with([
            'student:id,nama_lengkap,nis,class_id',
            'student.class:id,code',
            'invoice:id,invoice_number,total',
            'paymentMethod:id,name',
        ])
            ->where('school_id', $user->school_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->class_id, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('class_id', $request->class_id)))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('payment_number', 'like', "%{$request->search}%")
                        ->orWhereHas('student', fn($ss) => $ss->where('nama_lengkap', 'like', "%{$request->search}%"))
                        ->orWhereHas('invoice', fn($si) => $si->where('invoice_number', 'like', "%{$request->search}%"));
                });
            })
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->when($request->payment_channel, fn($q) => $q->where('payment_channel', $request->payment_channel))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $payments->through(function ($p) {
                return [
                    'id' => $p->id,
                    'payment_number' => $p->payment_number,
                    'student' => [
                        'id' => $p->student?->id,
                        'nama' => $p->student?->nama_lengkap,
                        'nis' => $p->student?->nis,
                        'kelas' => $p->student?->class?->code,
                    ],
                    'invoice' => [
                        'id' => $p->invoice?->id,
                        'number' => $p->invoice?->invoice_number,
                        'total' => (float) ($p->invoice?->total ?? 0),
                    ],
                    'amount' => (float) $p->amount,
                    'admin_fee' => (float) $p->admin_fee,
                    'payment_method' => $p->paymentMethod?->name ?? $p->payment_channel,
                    'payment_channel' => $p->payment_channel,
                    'status' => $p->status,
                    'payment_date' => $p->payment_date?->format('Y-m-d'),
                    'verified_at' => $p->verified_at?->toISOString(),
                    'verifier' => $p->verifier?->name,
                    'proof_file' => $p->proof_file,
                    'notes' => $p->notes,
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

    /**
     * Verifikasi pembayaran
     */
    public function verify(Request $request, Payment $payment): JsonResponse
    {
        $user = $request->user();

        if ($payment->status !== 'pending') {
            return response()->json(['success' => false, 'message' => "Pembayaran ini sudah {$payment->status}"], 400);
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'verified',
                'verified_by' => $user->id,
                'verified_at' => now(),
                'paid_at' => now(),
            ]);

            // Log verifikasi
            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'verify',
                'note' => "Pembayaran diverifikasi oleh {$user->name}",
                'user_id' => $user->id,
            ]);

            // Update status invoice
            $invoice = $payment->invoice;
            if ($invoice) {
                $paidAmount = (float) $invoice->payments()->where('status', 'verified')->sum('amount');
                $invoice->update([
                    'status' => $paidAmount >= (float) $invoice->total ? 'paid' : 'partial',
                    'paid_at' => $paidAmount >= (float) $invoice->total ? now() : $invoice->paid_at,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diverifikasi',
                'data' => $payment->fresh()->load('invoice'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal verifikasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tolak pembayaran
     */
    public function reject(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($payment->status !== 'pending') {
            return response()->json(['success' => false, 'message' => "Pembayaran ini sudah {$payment->status}"], 400);
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'rejected',
                'reject_reason' => $validated['reason'],
            ]);

            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'reject',
                'note' => $validated['reason'],
                'user_id' => $request->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ditolak',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menolak: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Laporan keuangan (ringkasan)
     */
    public function report(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $validated['date_to'] ?? now()->endOfMonth()->format('Y-m-d');

        $paymentsQuery = Payment::where('school_id', $user->school_id)
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        if (!empty($validated['class_id'])) {
            $paymentsQuery->whereHas('student', fn($q) => $q->where('class_id', $validated['class_id']));
        }

        // Ringkasan
        $totalPaid = (float) (clone $paymentsQuery)->where('status', 'verified')->sum('amount');
        $totalPending = (float) (clone $paymentsQuery)->where('status', 'pending')->sum('amount');
        $totalRejected = (float) (clone $paymentsQuery)->where('status', 'rejected')->sum('amount');
        $totalTransactions = (clone $paymentsQuery)->count();
        $verifiedCount = (clone $paymentsQuery)->where('status', 'verified')->count();

        // Per channel
        $byChannel = (clone $paymentsQuery)->where('status', 'verified')
            ->selectRaw('payment_channel, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_channel')
            ->get();

        // Per bulan
        $byMonth = (clone $paymentsQuery)->where('status', 'verified')
            ->selectRaw("strftime('%Y-%m', payment_date) as month, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Tagihan outstanding
        $totalInvoiced = (float) Invoice::where('school_id', $user->school_id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('total');
        $totalCollected = (float) Payment::where('school_id', $user->school_id)
            ->where('status', 'verified')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'total_verified' => $totalPaid,
                'total_pending' => $totalPending,
                'total_rejected' => $totalRejected,
                'total_transactions' => $totalTransactions,
                'verified_count' => $verifiedCount,
                'collection_rate' => $totalInvoiced > 0 ? round(($totalCollected / ($totalCollected + $totalInvoiced)) * 100, 2) : 100,
            ],
            'by_channel' => $byChannel,
            'by_month' => $byMonth,
        ]);
    }
}
