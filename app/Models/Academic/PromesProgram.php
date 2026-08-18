<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromesProgram extends SchoolModel
{
    protected $table = 'promes_programs';

    protected $fillable = [
        'school_id', 'staff_id', 'subject_id', 'semester_id',
        'week_number', 'activity_description', 'allocation_hours', 'status',
    ];

    protected $casts = [
        'week_number'      => 'integer',
        'allocation_hours' => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
