<?php

namespace App\Models\Event;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolEvent extends SchoolModel
{
    protected $table = 'school_events';

    protected $fillable = [
        'school_id','title','slug','description','event_type','starts_at','ends_at',
        'venue','city','venue_lat','venue_lng','capacity','ticket_price',
        'target_audience','cover_image_path','require_rsvp','is_published',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'capacity'         => 'integer',
        'ticket_price'     => 'integer',
        'venue_lat'        => 'decimal:7',
        'venue_lng'        => 'decimal:7',
        'target_audience'  => 'array',
        'require_rsvp'     => 'boolean',
        'is_published'     => 'boolean',
    ];

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }
}
