<?php

namespace App\Models\Visitor;

use App\Models\SchoolModel;

class VisitorBlacklist extends SchoolModel
{
    protected $table = 'visitor_blacklist';

    protected $fillable = ['school_id', 'id_number', 'full_name', 'reason', 'added_by'];
}
