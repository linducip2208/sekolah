<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsReport extends SchoolModel
{
    protected $table = 'bpjs_reports';

    protected $fillable = [
        'school_id', 'month', 'staff_id', 'salary_base',
        'kesehatan_employee', 'kesehatan_employer',
        'jkk', 'jkm',
        'jht_employee', 'jht_employer',
        'jp_employee', 'jp_employer',
        'total_employee', 'total_employer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Staff::class, 'staff_id');
    }
}
