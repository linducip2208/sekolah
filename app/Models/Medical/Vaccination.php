<?php

namespace App\Models\Medical;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends SchoolModel
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    protected $table = 'vaccinations';

    protected $fillable = [
        'school_id','student_id','vaccine_name','vaccinated_at',
        'batch_number','administered_by','next_dose_due','certificate_path',
    ];

    protected $casts = [
        'vaccinated_at' => 'date',
        'next_dose_due' => 'date',
    ];
}
