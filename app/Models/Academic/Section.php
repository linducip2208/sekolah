<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;

class Section extends SchoolModel
{
    protected $fillable = ['school_id', 'name'];
}
