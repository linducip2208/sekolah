<?php

namespace App\Models\RoomBooking;

use App\Models\SchoolModel;
use App\Models\User;

class RoomBooking extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'bookable_room_id',
        'user_id',
        'title',
        'purpose',
        'date',
        'start_time',
        'end_time',
        'status',
        'approved_by',
        'rejection_reason',
        'is_recurring',
        'recurring_pattern',
        'recurring_until',
        'calendar_event_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'bool',
        'recurring_until' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(BookableRoom::class, 'bookable_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
