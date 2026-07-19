<?php

namespace App\Models\Academic;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonStudyObservation extends Model
{
    protected $fillable = [
        'lesson_study_id', 'observer_id', 'observation_type',
        'notes', 'rating', 'observed_at',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'observed_at' => 'datetime',
    ];

    public function lessonStudy(): BelongsTo
    {
        return $this->belongsTo(LessonStudy::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }
}
