<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BkkPartner extends SchoolModel
{
    protected $table = 'bkk_partners';

    protected $fillable = [
        'school_id', 'company_name', 'industry_type', 'contact_person',
        'phone', 'email', 'address', 'mou_status', 'mou_file_path',
        'mou_start_date', 'mou_end_date', 'partnership_level',
    ];

    protected $casts = [
        'mou_start_date' => 'date',
        'mou_end_date' => 'date',
    ];

    public function placements(): HasMany
    {
        return $this->hasMany(BkkPlacement::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(BkkApplication::class);
    }
}
