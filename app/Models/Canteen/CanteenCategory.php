<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;

class CanteenCategory extends SchoolModel
{
    protected $table = 'canteen_categories';

    protected $fillable = ['school_id','name','icon','healthy_tag'];

    protected $casts = ['healthy_tag' => 'boolean'];
}
