<?php

namespace App\Models\Website;

use App\Models\SchoolModel;

class SchoolPageSection extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'school_page_id',
        'section_type',
        'title',
        'subtitle',
        'content',
        'image_path',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(SchoolPage::class, 'school_page_id');
    }
}
