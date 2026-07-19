<?php

namespace App\Models\Facilities;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransport extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'transport_route_id', 'transport_route_stop_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(TransportRouteStop::class, 'transport_route_stop_id');
    }
}
