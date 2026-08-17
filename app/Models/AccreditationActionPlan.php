<?php

namespace App\Models;

use App\Models\Academic\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationActionPlan extends SchoolModel
{
    protected $table = 'accreditation_action_plans';

    protected $fillable = [
        'school_id', 'accreditation_standard_id', 'accreditation_instrument_id',
        'title', 'action', 'responsible_id', 'due_date', 'status', 'notes',
    ];

    protected $casts = ['due_date' => 'date'];

    public function standard(): BelongsTo
    {
        return $this->belongsTo(AccreditationStandard::class, 'accreditation_standard_id');
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AccreditationInstrument::class, 'accreditation_instrument_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
