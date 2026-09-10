<?php

namespace App\Models\Visitor;

use App\Models\SchoolTenantModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorBlacklistEntry extends SchoolTenantModel
{
    protected $table = 'visitor_blacklists';

    protected $fillable = [
        'school_id', 'visitor_id', 'identity_type', 'identity_number',
        'full_name', 'reason', 'is_active', 'added_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
