<?php

namespace App\Mail;

use App\Models\PPDB\PpdbApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PpdbApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Status Pendaftaran PPDB',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ppdb-rejection',
            with: ['application' => $this->application],
        );
    }
}
