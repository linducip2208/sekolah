<?php

namespace App\Mail;

use App\Models\Scholarship\ScholarshipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScholarshipGrantedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ScholarshipApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎓 Selamat! Beasiswa Anak Anda Diterima',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.scholarship-granted',
            with: ['application' => $this->application],
        );
    }
}
