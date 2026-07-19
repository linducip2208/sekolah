<?php

namespace App\Models\Visitor;

use App\Models\SchoolModel;

class VisitorLog extends SchoolModel
{
    protected $table = 'visitor_logs';

    protected $fillable = [
        'school_id','visitor_name','id_number','phone','photo_path','purpose',
        'host_user_id','badge_no','checked_in_at','checked_out_at',
        'logged_by','items_carried','is_blacklisted','note',
    ];

    protected $casts = [
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
        'items_carried'  => 'array',
        'is_blacklisted' => 'boolean',
    ];
}
