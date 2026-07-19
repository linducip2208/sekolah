<?php

namespace App\Http\Controllers\Web\Parent;

use App\Http\Controllers\Controller;
use App\Models\Finance\FeeInvoice;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentTransaction;
use App\Services\Payment\Exceptions\GatewayException;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentPaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    public function invoices(Request $request): View
    {
        $user = $request->user();

        $studentIds = $user->hasRole('parent')
            ? $user->parentStudents()->pluck('students.id')
            : ($user->student?->id ? collect([$user->student->id]) : collect());

        $invoices = FeeInvoice::where('school_id', $user->school_id)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->get();

        return view('parent-portal.payment.invoices', compact('invoices'));
    }

    public function choose(Request $request, int $invoiceId): View
    {
        $user = $request->user();

        $invoice = FeeInvoice::where('school_id', $user->school_id)->findOrFail($invoiceId);

        $methods = PaymentMethod::with('provider')
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->get();

        return view('parent-portal.payment.choose', compact('invoice', 'methods'));
    }

    public function initiate(Request $request, int $invoiceId): RedirectResponse
    {
        $request->validate(['payment_method_id' => 'required|integer']);
        $user = $request->user();

        $invoice = FeeInvoice::where('school_id', $user->school_id)->findOrFail($invoiceId);
        $method  = PaymentMethod::where('school_id', $user->school_id)
            ->findOrFail($request->input('payment_method_id'));

        try {
            $tx = $this->payments->initiate($invoice, $method, $user, $request->session()->getId());
        } catch (GatewayException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect()->route('portal.payments.show', $tx->reference_no);
    }

    public function show(Request $request, string $referenceNo): View
    {
        $tx = PaymentTransaction::where('school_id', $request->user()->school_id)
            ->where('reference_no', $referenceNo)
            ->firstOrFail();

        return view('parent-portal.payment.show', compact('tx'));
    }

    public function cancel(Request $request, string $referenceNo): RedirectResponse
    {
        $tx = PaymentTransaction::where('school_id', $request->user()->school_id)
            ->where('reference_no', $referenceNo)
            ->firstOrFail();

        if ($tx->initiated_by !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->payments->cancel($tx);
        } catch (GatewayException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect()->route('portal.invoices')->with('success', 'Transaksi dibatalkan.');
    }

    public function returnFromGateway(Request $request): View
    {
        $referenceNo = $request->query('ref');
        $tx = $referenceNo
            ? PaymentTransaction::where('reference_no', $referenceNo)->first()
            : null;

        return view('parent-portal.payment.return', compact('tx'));
    }
}
