<?php

namespace App\Models\Achievement;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends SchoolModel
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(AchievementCategory::class, 'achievement_category_id');
    }

    protected $table = 'student_achievements';

    protected $fillable = [
        'school_id','student_id','achievement_category_id',
        'title','achieved_at','issuer','certificate_path','description',
        'verified','verified_by',
    ];

    protected $casts = [
        'achieved_at' => 'date',
        'verified'    => 'boolean',
    ];
}
