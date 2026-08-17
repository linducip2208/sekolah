<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyMaterial extends SchoolModel
{
    protected $fillable = ['school_id', 'lesson_id', 'title', 'type', 'url'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
