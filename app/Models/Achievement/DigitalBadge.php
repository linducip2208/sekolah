<?php

namespace App\Models\Achievement;

use App\Models\SchoolModel;

class DigitalBadge extends SchoolModel
{
    protected $table = 'digital_badges';

    protected $fillable = ['school_id','name','icon_path','description','award_criteria'];

    protected $casts = ['award_criteria' => 'array'];
}
