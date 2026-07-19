<?php

namespace App\Models\Finance;

use App\Models\Academic\ClassRoom;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends SchoolModel
{
    protected $fillable = [
        'school_id', 'class_room_id', 'name', 'frequency', 'amount', 'is_active',
    ];

    protected $casts = [
        'amount'    => 'integer',
        'is_active' => 'boolean',
    ];

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
