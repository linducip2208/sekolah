<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelMessMenu extends SchoolModel
{
    protected $fillable = [
        'school_id', 'hostel_id', 'day_of_week', 'meal_type',
        'menu_description', 'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active'   => 'boolean',
    ];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }
}
