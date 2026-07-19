<?php

namespace App\Mail;

use App\Models\Payment\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PaymentTransaction $tx) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Pembayaran SPP Berhasil — ' . $this->tx->reference_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-receipt',
            with: [
                'tx'      => $this->tx,
                'invoice' => $this->tx->invoice,
                'student' => $this->tx->invoice?->student,
            ],
        );
    }
}
