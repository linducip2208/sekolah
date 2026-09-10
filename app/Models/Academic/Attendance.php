<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends SchoolModel
{
    use AuditableModel;

    protected $fillable = [
        'school_id', 'student_id', 'class_section_id', 'marked_by',
        'date', 'status', 'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
