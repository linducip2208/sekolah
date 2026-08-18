<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelRoom extends SchoolModel
{
    protected $fillable = ['school_id', 'hostel_id', 'room_no', 'capacity', 'occupied', 'status', 'fee_per_month'];

    protected $casts = [
        'capacity'      => 'integer',
        'occupied'      => 'integer',
        'fee_per_month' => 'integer',
    ];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(HostelBed::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(HostelAttendance::class);
    }
}
