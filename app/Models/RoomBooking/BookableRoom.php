<?php

namespace App\Models\RoomBooking;

use App\Models\SchoolModel;

class BookableRoom extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'name',
        'room_type',
        'capacity',
        'floor',
        'building',
        'facilities',
        'is_active',
        'description',
        'photo_path',
    ];

    protected $casts = [
        'facilities' => 'array',
        'is_active' => 'bool',
    ];

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class, 'bookable_room_id');
    }

    public function rules()
    {
        return $this->hasMany(RoomBookingRule::class, 'bookable_room_id');
    }
}
