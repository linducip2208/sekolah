<?php

namespace App\Models\Activity;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentActivityLog extends SchoolModel
{
    protected $table = 'student_activity_log';

    protected $fillable = [
        'school_id', 'student_id', 'activity_type', 'title',
        'description', 'reference_type', 'reference_id', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
