<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;

class AssetCategory extends SchoolModel
{
    protected $table = 'asset_categories';

    protected $fillable = ['school_id', 'name', 'icon'];
}
