<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends SchoolModel
{
    protected $table = 'device_tokens';

    protected $fillable = [
        'school_id', 'user_id', 'token', 'platform', 'device_name', 'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
