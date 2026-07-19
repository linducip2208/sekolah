<?php

namespace App\Models\Website;

use App\Models\SchoolModel;

class SchoolContact extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'name',
        'email',
        'phone',
        'message',
        'is_read',
        'replied_at',
    ];

    protected $casts = [
        'is_read' => 'bool',
        'replied_at' => 'datetime',
    ];
}
