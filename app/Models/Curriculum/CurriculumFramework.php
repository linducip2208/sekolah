<?php

namespace App\Models\Curriculum;

use App\Models\SchoolModel;

class CurriculumFramework extends SchoolModel
{
    protected $table = 'curriculum_frameworks';

    protected $fillable = ['school_id','name','type','config','is_active'];

    protected $casts = ['config' => 'array', 'is_active' => 'boolean'];
}
