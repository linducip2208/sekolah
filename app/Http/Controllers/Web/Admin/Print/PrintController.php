<?php

namespace App\Http\Controllers\Web\Admin\Print;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Finance\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PrintController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }
    private function authorizeOwn($model): void { abort_unless($model->school_id === $this->schoolId(), 403); }

    /** Cetak invoice SPP */
    public function invoice(FeeInvoice $invoice): Response
    {
        $this->authorizeOwn($invoice);
        $invoice->load(['student.user', 'student.classSection.classRoom', 'student.classSection.section', 'feeStructure']);
        $school = \App\Models\School::find($this->schoolId());
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'school'))->setPaper('A4');
        return $pdf->stream("invoice-{$invoice->invoice_no}.pdf");
    }

    /** Cetak kuitansi pembayaran (per payment) */
    public function paymentReceipt(FeePayment $payment): Response
    {
        $invoice = $payment->invoice()->with(['student.user', 'feeStructure'])->first();
        $this->authorizeOwn($invoice);
        $school = \App\Models\School::find($this->schoolId());
        $pdf = Pdf::loadView('pdf.payment-receipt-fee', compact('payment', 'invoice', 'school'))->setPaper('A5', 'landscape');
        return $pdf->stream("kuitansi-{$payment->id}.pdf");
    }

    /** Cetak slip gaji */
    public function salarySlip(SalarySlip $slip): Response
    {
        $this->authorizeOwn($slip);
        $slip->load('staff.user');
        $school = \App\Models\School::find($this->schoolId());
        $pdf = Pdf::loadView('pdf.salary-slip', compact('slip', 'school'))->setPaper('A4');
        return $pdf->stream("slip-gaji-{$slip->month}-{$slip->staff_id}.pdf");
    }

    /** Cetak ID Card siswa */
    public function idCard(Student $student): Response
    {
        $this->authorizeOwn($student);
        $student->load(['user', 'classSection.classRoom', 'classSection.section']);
        $school = \App\Models\School::find($this->schoolId());
        $pdf = Pdf::loadView('pdf.id-card', compact('student', 'school'))->setPaper([0, 0, 245, 153], 'landscape'); // 86×54 mm
        return $pdf->stream("id-{$student->admission_no}.pdf");
    }

    /** Cetak raport (semester terakhir) */
    public function reportCard(Student $student): Response
    {
        $this->authorizeOwn($student);
        $student->load(['user', 'classSection.classRoom', 'classSection.section']);
        $school = \App\Models\School::find($this->schoolId());
        $marks = \App\Models\Academic\Mark::where('student_id', $student->id)
            ->with(['subject:id,name', 'exam:id,title'])
            ->orderByDesc('created_at')->get()
            ->groupBy('subject.name');
        $pdf = Pdf::loadView('pdf.report-card-simple', compact('student', 'school', 'marks'))->setPaper('A4');
        return $pdf->stream("raport-{$student->admission_no}.pdf");
    }
}
