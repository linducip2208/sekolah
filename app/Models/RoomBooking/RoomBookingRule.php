<?php

namespace App\Models\RoomBooking;

use App\Models\SchoolModel;

class RoomBookingRule extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'bookable_room_id',
        'rule_type',
        'rule_value',
    ];

    public function room()
    {
        return $this->belongsTo(BookableRoom::class, 'bookable_room_id');
    }
}
