<?php

namespace App\Models\PPDB;

use App\Models\SchoolModel;

class PpdbZonasiZone extends SchoolModel
{
    protected $table = 'ppdb_zonasi_zones';

    protected $fillable = [
        'school_id', 'district', 'subdistrict', 'priority_score',
    ];

    protected $casts = [
        'priority_score' => 'decimal:2',
    ];
}
