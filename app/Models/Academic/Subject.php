<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends SchoolModel
{
    protected $fillable = [
        'school_id', 'medium_id', 'name', 'code', 'type', 'credit_hours', 'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'credit_hours' => 'integer',
    ];

    public function medium(): BelongsTo
    {
        return $this->belongsTo(Medium::class, 'medium_id', 'id', 'mediums');
    }
}
