<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'type', 'warden_name', 'warden_phone',
        'warden_email', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class);
    }

    public function messMenus(): HasMany
    {
        return $this->hasMany(HostelMessMenu::class);
    }
}
