<?php

namespace App\Models\Curriculum;

use App\Models\Academic\ClassRoom;
use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumCompetency extends SchoolModel
{
    protected $table = 'curriculum_competencies';

    public const LEVELS = ['cp' => 'Capaian Pembelajaran', 'tp' => 'Tujuan Pembelajaran', 'atp' => 'Alur Tujuan Pembelajaran'];

    protected $fillable = [
        'school_id','curriculum_framework_id','subject_id','class_room_id',
        'code','description','level_type','parent_id','indicators',
    ];

    protected $casts = ['indicators' => 'array'];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(CurriculumFramework::class, 'curriculum_framework_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
