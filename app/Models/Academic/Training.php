<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends SchoolModel
{
    protected $fillable = [
        'school_id', 'title', 'provider', 'training_type',
        'start_date', 'end_date', 'duration_hours', 'location',
        'certificate_template', 'is_mandatory', 'description',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'duration_hours' => 'integer',
        'is_mandatory'   => 'boolean',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(TrainingParticipant::class);
    }
}
