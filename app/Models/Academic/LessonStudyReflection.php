<?php

namespace App\Models\Academic;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonStudyReflection extends Model
{
    protected $fillable = [
        'lesson_study_id', 'staff_id', 'reflection_text',
        'strength_points', 'improvement_points', 'action_plan',
    ];

    public function lessonStudy(): BelongsTo
    {
        return $this->belongsTo(LessonStudy::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
