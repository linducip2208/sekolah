<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPoint extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'points', 'reason',
        'point_type', 'reference_type', 'reference_id',
        'awarded_by', 'awarded_at',
    ];

    protected $casts = [
        'points'    => 'integer',
        'awarded_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
