<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentObservation extends SchoolModel
{
    protected $table = 'student_observations';

    protected $fillable = [
        'school_id', 'student_id', 'observer_id', 'subject_id',
        'rubric_id', 'date', 'observation_type', 'overall_notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'observer_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ObservationScore::class, 'observation_id');
    }
}
