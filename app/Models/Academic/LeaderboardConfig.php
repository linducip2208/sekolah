<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;

class LeaderboardConfig extends SchoolModel
{
    protected $table = 'leaderboard_configs';

    protected $fillable = [
        'school_id', 'config_type', 'is_active',
        'weight_academic', 'weight_attendance',
        'weight_extracurricular', 'weight_discipline',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'weight_academic'         => 'integer',
        'weight_attendance'       => 'integer',
        'weight_extracurricular'   => 'integer',
        'weight_discipline'       => 'integer',
    ];

    public function getTotalWeightAttribute(): int
    {
        return $this->weight_academic
            + $this->weight_attendance
            + $this->weight_extracurricular
            + $this->weight_discipline;
    }
}
