<?php

namespace App\Mail;

use App\Models\PPDB\PpdbApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbAcceptanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PpdbApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Selamat! Anak Anda Diterima',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ppdb-acceptance',
            with: ['application' => $this->application],
        );
    }
}
