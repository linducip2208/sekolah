<?php

namespace App\Models\Achievement;

use App\Models\SchoolModel;

class AchievementCategory extends SchoolModel
{
    protected $table = 'achievement_categories';

    protected $fillable = ['school_id','name','scope','points'];

    protected $casts = ['points' => 'integer'];
}
