<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentTag extends SchoolModel
{
    protected $table = 'student_tags';

    protected $fillable = ['school_id', 'name', 'color'];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_tag_pivot');
    }
}
