<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PkgObservation extends Model
{
    use SoftDeletes;

    protected $table = 'pkg_observations';

    protected $fillable = [
        'pkg_assessment_id', 'class_section_id', 'subject_id',
        'observation_date', 'observation_notes',
        'class_atmosphere', 'student_engagement',
    ];

    protected $casts = [
        'observation_date' => 'date',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(PkgAssessment::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
