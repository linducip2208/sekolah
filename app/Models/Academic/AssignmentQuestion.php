<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'assignment_questions';

    protected $fillable = [
        'assignment_id', 'question_number', 'question_text',
        'question_type', 'options', 'correct_answer', 'points',
    ];

    protected $casts = [
        'options'  => 'array',
        'points'   => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}
