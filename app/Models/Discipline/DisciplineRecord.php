<?php

namespace App\Models\Discipline;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineRecord extends SchoolModel
{
    protected $table = 'discipline_records';

    protected $fillable = [
        'school_id','student_id','discipline_category_id','reported_by',
        'incident_date','description','evidence_files','points',
        'status','sanction_applied','parent_notified',
    ];

    protected $casts = [
        'incident_date'   => 'date',
        'evidence_files'  => 'array',
        'points'          => 'integer',
        'parent_notified' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DisciplineCategory::class, 'discipline_category_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
