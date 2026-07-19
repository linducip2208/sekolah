<?php

namespace App\Models\Website;

use App\Models\SchoolModel;

class SchoolGallery extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'title',
        'file_path',
        'caption',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'bool',
    ];
}
