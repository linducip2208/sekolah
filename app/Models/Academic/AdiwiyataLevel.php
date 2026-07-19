<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;

class AdiwiyataLevel extends SchoolModel
{
    protected $table = 'adiwiyata_levels';

    protected $fillable = [
        'school_id', 'achieved_level', 'achieved_date',
        'certificate_number', 'certificate_file', 'notes',
    ];

    protected $casts = [
        'achieved_date' => 'date',
    ];
}
