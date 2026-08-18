<?php

namespace App\Models\Foundation;

use App\Models\SchoolModel;

class FoundationMasterData extends SchoolModel
{
    protected $table = 'foundation_master_data';

    protected $fillable = [
        'foundation_id', 'data_type', 'data_json', 'is_synced',
    ];

    protected $casts = [
        'data_json' => 'array',
        'is_synced' => 'boolean',
    ];
}
