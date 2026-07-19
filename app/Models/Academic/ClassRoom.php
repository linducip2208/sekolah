<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRoom extends SchoolModel
{
    protected $fillable = ['school_id', 'medium_id', 'name'];

    public function medium(): BelongsTo
    {
        return $this->belongsTo(Medium::class, 'medium_id', 'id', 'mediums');
    }
}
