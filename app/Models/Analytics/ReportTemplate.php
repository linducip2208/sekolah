<?php

namespace App\Models\Analytics;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplate extends SchoolModel
{
    protected $fillable = [
        'school_id', 'user_id', 'name', 'description',
        'report_type', 'config', 'is_shared',
    ];

    protected $casts = [
        'config'    => 'array',
        'is_shared' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedReports(): HasMany
    {
        return $this->hasMany(SavedReport::class);
    }
}
