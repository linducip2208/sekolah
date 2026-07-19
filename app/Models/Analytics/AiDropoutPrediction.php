<?php

namespace App\Models\Analytics;

use App\Models\Academic\Student;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDropoutPrediction extends SchoolModel
{
    protected $table = 'ai_dropout_predictions';

    protected $fillable = [
        'school_id', 'student_id', 'prediction_date', 'risk_level',
        'risk_score', 'contributing_factors', 'ai_analysis',
        'ai_provider_id', 'ai_model_id', 'recommended_actions',
        'notified_parents', 'notified_teacher', 'tokens_used', 'processing_time_ms',
    ];

    protected $casts = [
        'prediction_date'     => 'date',
        'risk_score'          => 'decimal:2',
        'contributing_factors'=> 'array',
        'notified_parents'    => 'boolean',
        'notified_teacher'    => 'boolean',
        'tokens_used'         => 'integer',
        'processing_time_ms'  => 'integer',
    ];

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

    public function riskLevelColor(): string
    {
        return match ($this->risk_level) {
            'critical' => '#DC2626',
            'high'     => '#EA580C',
            'medium'   => '#EAB308',
            default    => '#16A34A',
        };
    }

    public function riskLevelBadgeClass(): string
    {
        return match ($this->risk_level) {
            'critical' => 'bg-red-100 text-red-800',
            'high'     => 'bg-orange-100 text-orange-800',
            'medium'   => 'bg-yellow-100 text-yellow-800',
            default    => 'bg-green-100 text-green-800',
        };
    }
}
