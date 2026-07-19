<?php

namespace App\Models\Achievement;

use App\Models\SchoolModel;

class StudentBadge extends SchoolModel
{
    protected $table = 'student_badges';

    protected $fillable = ['school_id','student_id','digital_badge_id','awarded_at'];

    protected $casts = ['awarded_at' => 'date'];
}
