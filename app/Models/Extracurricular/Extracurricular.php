<?php

namespace App\Models\Extracurricular;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Extracurricular extends SchoolModel
{
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    protected $table = 'extracurriculars';

    protected $fillable = [
        'school_id','name','icon','description','coach_id',
        'schedule','capacity','fee_per_month','is_active',
    ];

    protected $casts = [
        'schedule'      => 'array',
        'capacity'      => 'integer',
        'fee_per_month' => 'integer',
        'is_active'     => 'boolean',
    ];
}
