<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;

class Pph21Bracket extends SchoolModel
{
    protected $table = 'pph21_brackets';

    protected $fillable = [
        'school_id', 'min_annual', 'max_annual', 'rate_pct',
    ];

    protected $casts = [
        'min_annual' => 'integer',
        'max_annual' => 'integer',
    ];
}
