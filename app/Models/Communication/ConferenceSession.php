<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConferenceSession extends SchoolModel
{
    protected $fillable = [
        'school_id', 'created_by', 'title', 'description',
        'conference_type', 'date', 'start_time', 'end_time',
        'duration_minutes', 'max_bookings', 'location',
        'location_detail', 'meeting_link', 'is_published',
    ];

    protected $casts = [
        'date'             => 'date',
        'duration_minutes' => 'integer',
        'max_bookings'     => 'integer',
        'is_published'     => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ConferenceBooking::class);
    }

    public function isFullyBooked(): bool
    {
        if (!$this->max_bookings) {
            return false;
        }
        return $this->bookings()->whereNotIn('status', ['cancelled'])->count() >= $this->max_bookings;
    }

    public function timeSlots(): array
    {
        $slots = [];
        $start = strtotime($this->start_time);
        $end   = strtotime($this->end_time);
        $dur   = $this->duration_minutes * 60;

        while ($start + $dur <= $end) {
            $slot = date('H:i', $start);
            $booked = $this->bookings()
                ->where('booking_time', $slot)
                ->whereNotIn('status', ['cancelled'])
                ->count();
            $slots[] = [
                'time'   => $slot,
                'booked' => $booked,
                'max'    => $this->max_bookings,
                'available' => !$this->max_bookings || $booked < $this->max_bookings,
            ];
            $start += $dur;
        }

        return $slots;
    }
}
