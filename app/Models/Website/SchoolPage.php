<?php

namespace App\Models\Website;

use App\Models\SchoolModel;

class SchoolPage extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'title',
        'slug',
        'meta_description',
        'status',
        'is_homepage',
        'sort_order',
    ];

    protected $casts = [
        'is_homepage' => 'bool',
    ];

    public function sections()
    {
        return $this->hasMany(SchoolPageSection::class, 'school_page_id')->orderBy('sort_order');
    }
}
