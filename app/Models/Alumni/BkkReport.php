<?php

namespace App\Models\Alumni;

use App\Models\Academic\AcademicYear;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkkReport extends SchoolModel
{
    protected $table = 'bkk_reports';

    protected $fillable = [
        'school_id', 'academic_year_id', 'semester', 'total_graduates',
        'total_placed', 'total_entrepreneur', 'total_university',
        'total_unemployed', 'report_date', 'report_file_path', 'status',
    ];

    protected $casts = [
        'semester' => 'integer',
        'total_graduates' => 'integer',
        'total_placed' => 'integer',
        'total_entrepreneur' => 'integer',
        'total_university' => 'integer',
        'total_unemployed' => 'integer',
        'report_date' => 'date',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
