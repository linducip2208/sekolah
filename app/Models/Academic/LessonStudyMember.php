<?php

namespace App\Models\Academic;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonStudyMember extends Model
{
    protected $fillable = [
        'lesson_study_id', 'staff_id', 'role',
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
