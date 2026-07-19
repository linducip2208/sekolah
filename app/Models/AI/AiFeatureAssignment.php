<?php

namespace App\Models\AI;

use App\Models\SchoolModel;

class AiFeatureAssignment extends SchoolModel
{
    protected $table = 'ai_feature_assignments';

    protected $fillable = [
        'school_id','feature_key','ai_model_id','feature_config','is_enabled',
    ];

    protected $casts = [
        'feature_config' => 'array',
        'is_enabled'     => 'boolean',
    ];
}
