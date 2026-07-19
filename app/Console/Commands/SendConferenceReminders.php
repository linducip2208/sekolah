<?php

namespace App\Console\Commands;

use App\Models\Communication\ConferenceBooking;
use App\Services\ConferenceService;
use Illuminate\Console\Command;

class SendConferenceReminders extends Command
{
    protected $signature   = 'conference:send-reminders';
    protected $description = 'Kirim pengingat WhatsApp H-1 untuk konferensi orang tua-guru besok';

    public function __construct(private ConferenceService $conferenceService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $bookings = ConferenceBooking::whereIn('status', ['booked', 'confirmed'])
            ->whereHas('conferenceSession', fn($q) => $q->where('date', $tomorrow)->where('is_published', true))
            ->with(['conferenceSession.creator', 'student.user', 'parent'])
            ->get();

        foreach ($bookings as $booking) {
            $this->conferenceService->sendReminder($booking);
            $this->line("Reminder: {$booking->parent->name} — {$booking->conferenceSession->title}");
        }

        $this->info("{$bookings->count()} pengingat konferensi dikirim.");
        return self::SUCCESS;
    }
}
