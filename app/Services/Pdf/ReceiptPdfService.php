<?php

namespace App\Services\Pdf;

use App\Models\Branding\SchoolBranding;
use App\Models\Donation\Donation;
use App\Models\Payment\PaymentTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptPdfService
{
    public function paymentReceipt(PaymentTransaction $tx): Response
    {
        $branding = SchoolBranding::where('school_id', $tx->school_id)->first();

        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'tx'       => $tx,
            'invoice'  => $tx->invoice,
            'student'  => $tx->invoice?->student,
            'method'   => $tx->method,
            'school'   => $tx->invoice?->school,
            'branding' => $branding,
        ])->setPaper('A5', 'portrait');

        return $pdf->download("receipt-{$tx->reference_no}.pdf");
    }

    public function donationReceipt(Donation $donation): Response
    {
        $school   = \App\Models\School::find($donation->school_id);
        $branding = SchoolBranding::where('school_id', $donation->school_id)->first();

        $pdf = Pdf::loadView('pdf.donation-receipt', [
            'donation' => $donation,
            'campaign' => $donation->donationCampaign ?? null,
            'school'   => $school,
            'branding' => $branding,
        ])->setPaper('A5', 'portrait');

        return $pdf->download("donation-{$donation->receipt_no}.pdf");
    }

    public function ppdbAcceptanceLetter(\App\Models\PPDB\PpdbApplication $app): Response
    {
        $school   = \App\Models\School::find($app->school_id);
        $branding = SchoolBranding::where('school_id', $app->school_id)->first();

        $pdf = Pdf::loadView('pdf.ppdb-acceptance', [
            'app'      => $app,
            'school'   => $school,
            'branding' => $branding,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("acceptance-{$app->registration_no}.pdf");
    }

    public function achievementCertificate(
        \App\Models\Achievement\StudentAchievement $achievement,
        ?\App\Models\Achievement\CertificateTemplate $template = null,
    ): Response {
        $school   = \App\Models\School::find($achievement->school_id);
        $branding = SchoolBranding::where('school_id', $achievement->school_id)->first();

        $pdf = Pdf::loadView('pdf.certificate', [
            'achievement' => $achievement,
            'student'     => $achievement->student ?? null,
            'school'      => $school,
            'branding'    => $branding,
        ])->setPaper('A4', 'landscape');

        return $pdf->download("certificate-{$achievement->id}.pdf");
    }
}
