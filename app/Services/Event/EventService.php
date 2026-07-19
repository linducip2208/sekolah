<?php

namespace App\Services\Event;

use App\Models\Event\EventRsvp;
use App\Models\Event\SchoolEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    public function createEvent(int $schoolId, array $data): SchoolEvent
    {
        return SchoolEvent::create(array_merge($data, [
            'school_id' => $schoolId,
            'slug'      => Str::slug($data['title']) . '-' . Str::lower(Str::random(4)),
        ]));
    }

    public function rsvp(SchoolEvent $event, int $userId, string $status, int $guestsCount = 0): EventRsvp
    {
        return DB::transaction(function () use ($event, $userId, $status, $guestsCount) {
            if ($event->capacity) {
                $going = EventRsvp::where('school_event_id', $event->id)
                    ->where('status', 'going')
                    ->sum(DB::raw('1 + guests_count'));
                if ($status === 'going' && $going + 1 + $guestsCount > $event->capacity) {
                    throw new \RuntimeException('Kapasitas penuh');
                }
            }

            return EventRsvp::updateOrCreate(
                ['school_event_id' => $event->id, 'user_id' => $userId],
                [
                    'school_id'        => $event->school_id,
                    'guests_count'     => $guestsCount,
                    'status'           => $status,
                    'ticket_qr_token'  => $status === 'going' ? Str::random(48) : null,
                ],
            );
        });
    }

    public function checkIn(string $qrToken): EventRsvp
    {
        $rsvp = EventRsvp::where('ticket_qr_token', $qrToken)->firstOrFail();
        if ($rsvp->checked_in_at) {
            throw new \RuntimeException('Sudah check-in sebelumnya');
        }
        $rsvp->update(['checked_in_at' => now()]);
        return $rsvp->fresh();
    }
}
