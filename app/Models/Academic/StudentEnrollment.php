<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentEnrollment extends SchoolModel
{
    protected $table = 'student_enrollments';

    protected $fillable = [
        'school_id', 'student_id', 'from_class_section_id', 'to_class_section_id',
        'academic_year', 'status', 'effective_date', 'notes', 'approved_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    const STATUSES = ['enrolled', 'promoted', 'graduated', 'transferred', 'dropped'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClassSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'from_class_section_id');
    }

    public function toClassSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'to_class_section_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
