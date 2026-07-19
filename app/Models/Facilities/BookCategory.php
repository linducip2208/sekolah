<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;

class BookCategory extends SchoolModel
{
    protected $fillable = ['school_id', 'name'];
}
