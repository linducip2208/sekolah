<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtaProgram extends SchoolModel
{
    protected $table = 'prota_programs';

    protected $fillable = [
        'school_id', 'staff_id', 'subject_id', 'academic_year_id',
        'competencies', 'target_completion',
    ];

    protected $casts = [
        'competencies'     => 'array',
        'target_completion' => 'array',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
