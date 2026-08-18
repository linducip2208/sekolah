<?php

namespace App\Models\Foundation;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoundationUserManagement extends SchoolModel
{
    protected $table = 'foundation_user_management';

    protected $fillable = [
        'foundation_id', 'user_id', 'role', 'assigned_schools',
    ];

    protected $casts = [
        'assigned_schools' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
