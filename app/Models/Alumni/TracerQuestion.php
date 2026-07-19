<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TracerQuestion extends SchoolModel
{
    protected $table = 'tracer_questions';

    protected $fillable = [
        'school_id', 'question_text', 'question_type', 'options',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
