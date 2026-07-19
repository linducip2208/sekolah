<?php

namespace App\Services;

use App\Models\Communication\ConferenceBooking;
use App\Models\Communication\ConferenceSession;
use App\Services\Communication\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConferenceService
{
    public function __construct(private WhatsAppNotificationService $whatsapp) {}

    public function generateTimeSlots(ConferenceSession $session): array
    {
        return $session->timeSlots();
    }

    public function checkAvailability(ConferenceSession $session, string $timeSlot): bool
    {
        if (!$session->is_published) {
            return false;
        }

        $count = ConferenceBooking::where('conference_session_id', $session->id)
            ->where('booking_time', $timeSlot)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        if (!$session->max_bookings) {
            return true;
        }

        return $count < $session->max_bookings;
    }

    public function bookSlot(ConferenceSession $session, int $studentId, int $parentId, string $timeSlot, ?string $notes): ConferenceBooking
    {
        return DB::transaction(function () use ($session, $studentId, $parentId, $timeSlot, $notes) {
            $existing = ConferenceBooking::where('conference_session_id', $session->id)
                ->where('parent_id', $parentId)
                ->whereNotIn('status', ['cancelled'])
                ->first();

            if ($existing) {
                throw new \RuntimeException('Anda sudah memiliki booking untuk sesi ini.');
            }

            if (!$this->checkAvailability($session, $timeSlot)) {
                throw new \RuntimeException('Slot waktu sudah penuh.');
            }

            return ConferenceBooking::create([
                'school_id'              => $session->school_id,
                'conference_session_id'  => $session->id,
                'student_id'             => $studentId,
                'parent_id'              => $parentId,
                'booking_time'           => $timeSlot,
                'status'                 => 'booked',
                'notes'                  => $notes,
            ]);
        });
    }

    public function cancelBooking(ConferenceBooking $booking): void
    {
        $booking->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function sendReminder(ConferenceBooking $booking): void
    {
        $session = $booking->conferenceSession()->with('creator')->first();
        $student = $booking->student()->with('user')->first();
        $parent  = $booking->parent;

        if (!$parent || !$parent->phone) {
            return;
        }

        $date = Carbon::parse($session->date)->translatedFormat('l, d F Y');
        $location = $session->location === 'online'
            ? ($session->meeting_link ?: 'Link akan dikirimkan')
            : ($session->location_detail ?: 'Sekolah');

        $message = "*Pengingat Konferensi*\n\n"
            . "Halo {$parent->name},\n\n"
            . "Besok ({$date}) ada konferensi orang tua-guru:\n"
            . "*Acara:* {$session->title}\n"
            . "*Waktu:* {$booking->booking_time}\n"
            . "*Lokasi:* {$location}\n"
            . "*Anak:* {$student->user->name}\n\n"
            . "Mohon hadir tepat waktu. Terima kasih.\n"
            . "_{$session->createdBy?->name}_";

        $this->whatsapp->send($parent->phone, $message, $session->school_id);
    }
}
