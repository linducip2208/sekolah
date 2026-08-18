<?php

namespace App\Models\AI;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends SchoolModel
{
    protected $table = 'ai_recommendations';

    protected $fillable = [
        'school_id', 'student_id', 'type', 'risk_level', 'actions',
        'status', 'reviewed_by', 'reviewed_at', 'note',
    ];

    protected $casts = [
        'actions'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
