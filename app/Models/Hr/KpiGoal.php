<?php

namespace App\Models\Hr;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiGoal extends SchoolModel
{
    protected $table = 'kpi_goals';

    protected $fillable = [
        'school_id', 'staff_id', 'title', 'description', 'target_value',
        'actual_value', 'status', 'due_date',
    ];

    protected $casts = ['due_date' => 'date'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Staff::class, 'staff_id');
    }
}
