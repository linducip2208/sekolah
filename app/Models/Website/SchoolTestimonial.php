<?php

namespace App\Models\Website;

use App\Models\SchoolModel;

class SchoolTestimonial extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'name',
        'role',
        'photo_path',
        'testimonial_text',
        'rating',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'int',
        'is_published' => 'bool',
    ];
}
