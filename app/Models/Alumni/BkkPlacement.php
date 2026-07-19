<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkkPlacement extends SchoolModel
{
    protected $table = 'bkk_placements';

    protected $fillable = [
        'school_id', 'student_id', 'bkk_partner_id', 'job_listing_id',
        'position', 'placement_date', 'start_date', 'salary',
        'contract_type', 'status', 'supervisor_name', 'supervisor_phone',
    ];

    protected $casts = [
        'placement_date' => 'date',
        'start_date' => 'date',
        'salary' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(BkkPartner::class, 'bkk_partner_id');
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }
}
