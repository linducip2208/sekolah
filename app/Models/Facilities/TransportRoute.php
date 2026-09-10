<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends SchoolModel
{
    use AuditableModel;

    protected $fillable = ['school_id', 'name', 'fee_per_month', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function stops(): HasMany
    {
        return $this->hasMany(TransportRouteStop::class);
    }
}
