<?php

namespace App\Models\Transport;

use App\Models\Academic\Student;
use App\Models\Facilities\TransportRoute;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportAttendance extends SchoolModel
{
    protected $table = 'transport_attendances';

    protected $fillable = [
        'school_id', 'transport_route_id', 'student_id', 'date', 'direction', 'status', 'note',
    ];

    protected $casts = ['date' => 'date'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
