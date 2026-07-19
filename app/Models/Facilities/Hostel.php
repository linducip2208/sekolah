<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends SchoolModel
{
    protected $fillable = ['school_id', 'name', 'type', 'warden_name'];

    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class);
    }
}
