<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;

class Medium extends SchoolModel
{
    protected $table    = 'mediums';
    protected $fillable = ['school_id', 'name'];
}
