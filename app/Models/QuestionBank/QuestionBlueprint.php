<?php

namespace App\Models\QuestionBank;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBlueprint extends SchoolModel
{
    protected $table = 'question_blueprints';

    protected $fillable = [
        'school_id', 'name', 'exam_id', 'total_items', 'distribution',
    ];

    protected $casts = [
        'distribution' => 'array',
        'total_items'  => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Exam::class);
    }
}
