<?php

namespace App\Models\Hr;

use App\Models\Academic\Staff;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentContract extends SchoolModel
{
    protected $table = 'employment_contracts';

    protected $fillable = [
        'school_id', 'staff_id', 'type', 'start_date', 'end_date', 'salary', 'status', 'document_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'salary'     => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
