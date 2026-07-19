<?php

namespace App\Mail;

use App\Models\DailyReport\DailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DailyReport $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📅 Laporan Harian Anak — ' . $this->report->report_date->format('d M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-report',
            with: ['report' => $this->report],
        );
    }
}
