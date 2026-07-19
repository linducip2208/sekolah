<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;

class WaBotCommand extends SchoolModel
{
    protected $fillable = [
        'school_id', 'command_keyword', 'response_type',
        'static_response', 'function_method', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
