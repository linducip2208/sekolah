<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeRule extends Model
{
    protected $fillable = ['grade_system_id', 'grade', 'min_percent', 'max_percent', 'gpa_point'];

    public function gradeSystem(): BelongsTo
    {
        return $this->belongsTo(GradeSystem::class);
    }
}
