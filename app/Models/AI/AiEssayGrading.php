<?php

namespace App\Models\AI;

use App\Models\Academic\Exam;
use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEssayGrading extends SchoolModel
{
    protected $table = 'ai_essay_gradings';

    protected $fillable = [
        'school_id', 'exam_id', 'student_id', 'question_text',
        'student_answer', 'reference_answer', 'ai_provider_id', 'ai_model_id',
        'ai_score', 'ai_feedback', 'ai_rubric_breakdown',
        'tokens_used', 'processing_time_ms', 'graded_by', 'graded_at',
    ];

    protected $casts = [
        'ai_score'            => 'decimal:2',
        'ai_rubric_breakdown' => 'array',
        'tokens_used'         => 'integer',
        'processing_time_ms'  => 'integer',
        'graded_at'           => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function aiProvider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function scoreLabel(): string
    {
        $s = (float) $this->ai_score;
        return match (true) {
            $s >= 80 => 'Sangat Baik',
            $s >= 60 => 'Baik',
            $s >= 40 => 'Cukup',
            default  => 'Kurang',
        };
    }

    public function scoreColor(): string
    {
        $s = (float) $this->ai_score;
        return match (true) {
            $s >= 80 => '#16A34A',
            $s >= 60 => '#EAB308',
            default  => '#DC2626',
        };
    }
}
