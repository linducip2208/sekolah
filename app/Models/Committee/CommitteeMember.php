<?php

namespace App\Models\Committee;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends SchoolModel
{
    protected $fillable = [
        'school_id', 'user_id', 'role', 'period_start',
        'period_end', 'is_active',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'is_active'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
