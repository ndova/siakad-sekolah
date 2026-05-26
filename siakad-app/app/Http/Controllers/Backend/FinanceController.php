<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    protected function schoolId() { return auth()->user()->school_id; }

    public function feeTypes(Request $request)
    {
        $feeTypes = FeeType::where('school_id', $this->schoolId())
            ->when($request->category, fn($q,$c)=>$q->where('category',$c))
            ->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();
        return view('backend.finance.fee-types', compact('feeTypes'));
    }

    public function storeFeeType(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:fee_types,code',
            'name' => 'required|string|max:150',
            'category' => 'required|in:rutin,tidak_rutin',
            'nominal' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,semester,yearly',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;
        FeeType::create($data);
        return back()->with('success','Jenis biaya ditambahkan.');
    }

    public function invoices(Request $request)
    {
        $invoices = Invoice::with(['student.class','items','payments'])
            ->where('school_id', $this->schoolId())
            ->when($request->status, fn($q,$s)=>$q->where('status',$s))
            ->when($request->class_id, fn($q,$c)=>$q->whereHas('student',fn($q)=>$q->where('class_id',$c)))
            ->when($request->search, fn($q,$s)=>$q->where('invoice_number','like',"%$s%")
                ->orWhereHas('student',fn($q)=>$q->where('nama_lengkap','like',"%$s%")))
            ->orderBy('created_at','desc')->paginate(20)->withQueryString();

        $classes = SchoolClass::where('school_id', $this->schoolId())->where('is_active',true)->orderBy('code')->get();
        $feeTypes = FeeType::where('school_id', $this->schoolId())->where('is_active',true)->get();
        $academicYears = \App\Models\AcademicYear::where('school_id', $this->schoolId())->orderBy('code','desc')->get();
        $totalTagihan = Invoice::where('school_id', $this->schoolId())->sum('total');
        $totalTerbayar = Payment::where('school_id', $this->schoolId())->where('status','verified')->sum('amount');
        $totalTunggakan = max(0, $totalTagihan - $totalTerbayar);

        return view('backend.finance.invoices', compact('invoices','classes','feeTypes','academicYears','totalTagihan','totalTerbayar','totalTunggakan'));
    }

    public function generateInvoices(Request $request)
    {
        $data = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'class_ids' => 'required|array',
            'due_date' => 'required|date',
            'period_month' => 'nullable|integer|min:1|max:12',
            'period_year' => 'nullable|integer',
        ]);

        $feeType = FeeType::find($data['fee_type_id']);
        $students = Student::where('school_id', $this->schoolId())
            ->whereIn('class_id', $data['class_ids'])->where('status', 'aktif')->get();
        $batchId = (string) Str::uuid();
        $count = 0;

        foreach ($students as $student) {
            $invoice = Invoice::create([
                'school_id' => $this->schoolId(),
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::random(6),
                'student_id' => $student->id,
                'academic_year_id' => \App\Models\AcademicYear::where('school_id',$this->schoolId())->where('is_active',true)->value('id'),
                'batch_id' => $batchId, 'status' => 'unpaid',
                'subtotal' => $feeType->nominal, 'total' => $feeType->nominal,
                'due_date' => $data['due_date'], 'created_by' => auth()->id(),
            ]);
            InvoiceItem::create([
                'invoice_id' => $invoice->id, 'fee_type_id' => $feeType->id,
                'fee_name' => $feeType->name, 'quantity' => 1,
                'unit_price' => $feeType->nominal, 'subtotal' => $feeType->nominal,
                'period_month' => $data['period_month'] ?? null,
                'period_year' => $data['period_year'] ?? null,
            ]);
            $count++;
        }
        return back()->with('success',"$count tagihan berhasil digenerate.");
    }

    public function payments(Request $request)
    {
        $payments = Payment::with(['student.class','invoice','paymentMethod','verifier'])
            ->where('school_id', $this->schoolId())
            ->when($request->status, fn($q,$s)=>$q->where('status',$s))
            ->when($request->search, fn($q,$s)=>$q->where('payment_number','like',"%$s%")
                ->orWhereHas('student',fn($q)=>$q->where('nama_lengkap','like',"%$s%")))
            ->orderBy('created_at','desc')->paginate(20)->withQueryString();
        $invoices = Invoice::with('student')->where('school_id', $this->schoolId())
            ->whereIn('status', ['unpaid','partial'])->orderBy('due_date')->get();
        $methods = PaymentMethod::where('school_id', $this->schoolId())->where('is_active',true)->get();
        $pendingCount = Payment::where('school_id', $this->schoolId())->where('status','pending')->count();
        return view('backend.finance.payments', compact('payments','invoices','methods','pendingCount'));
    }

    public function storePayment(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'paid_by' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $invoice = Invoice::find($data['invoice_id']);
        $data['school_id'] = $this->schoolId();
        $data['student_id'] = $invoice->student_id;
        $data['payment_number'] = 'PAY-'.now()->format('Ymd').'-'.Str::random(6);
        $data['payment_channel'] = 'backend';
        $data['status'] = 'verified';
        $data['verified_by'] = auth()->id();
        $data['verified_at'] = now();
        $payment = Payment::create($data);
        $totalPaid = $invoice->payments()->where('status','verified')->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        }
        return back()->with('success','Pembayaran dicatat.');
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $action = $request->input('action');
        if ($action === 'verify') {
            $payment->update(['status'=>'verified', 'verified_by'=>auth()->id(), 'verified_at'=>now()]);
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->where('status','verified')->sum('amount');
            if ($totalPaid >= $invoice->total) $invoice->update(['status'=>'paid','paid_at'=>now()]);
            elseif ($totalPaid > 0) $invoice->update(['status'=>'partial']);
            return back()->with('success','Pembayaran diverifikasi.');
        }
        if ($action === 'reject') {
            $payment->update(['status'=>'rejected']);
            return back()->with('success','Pembayaran ditolak.');
        }
        return back();
    }

    public function reports(Request $request)
    {
        $month = $request->month ?? now()->format('m');
        $year = $request->year ?? now()->format('Y');
        $totalRevenue = Payment::where('school_id', $this->schoolId())
            ->where('status','verified')
            ->whereRaw("strftime('%m', payment_date) = ?", [$month])
            ->whereRaw("strftime('%Y', payment_date) = ?", [(string)$year])
            ->sum('amount');
        $totalUnpaid = Invoice::where('school_id', $this->schoolId())
            ->whereIn('status', ['unpaid','partial','overdue'])->sum('total')
            - Payment::where('school_id', $this->schoolId())->where('status','verified')->sum('amount');
        $byCategory = InvoiceItem::with('feeType')
            ->whereHas('invoice', fn($q)=>$q->where('school_id',$this->schoolId())
                ->whereHas('payments', fn($q)=>$q->where('status','verified')
                    ->whereRaw("strftime('%m', payment_date) = ?", [$month])
                    ->whereRaw("strftime('%Y', payment_date) = ?", [(string)$year])))
            ->get()->groupBy('fee_type_id')->map(fn($items)=>$items->sum('subtotal'));
        $monthlyData = Payment::where('school_id', $this->schoolId())
            ->where('status','verified')
            ->whereRaw("strftime('%Y', payment_date) = ?", [(string)$year])
            ->selectRaw("strftime('%m', payment_date) as m, SUM(amount) as total")
            ->groupBy('m')->pluck('total','m');
        return view('backend.finance.reports', compact('month','year','totalRevenue','totalUnpaid','byCategory','monthlyData'));
    }
}
