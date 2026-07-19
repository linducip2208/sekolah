<?php

namespace App\Mail;

use App\Models\Donation\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationThankYouMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Donation $donation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🙏 Terima Kasih atas Donasi Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.donation-thank-you',
            with: [
                'donation' => $this->donation,
                'campaign' => $this->donation->donationCampaign ?? null,
            ],
        );
    }
}
