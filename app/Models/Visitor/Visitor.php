<?php

namespace App\Models\Visitor;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'identity_type', 'identity_number', 'phone',
        'email', 'photo_path', 'company', 'notes',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(VisitorVisit::class);
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(VisitorBlacklistEntry::class);
    }
}
