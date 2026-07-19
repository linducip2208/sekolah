<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySlip extends SchoolModel
{
    protected $table = 'salary_slips';

    protected $fillable = [
        'school_id', 'staff_id', 'month', 'basic_salary',
        'total_allowances', 'total_deductions', 'net_salary',
        'allowances_detail', 'deductions_detail', 'status', 'paid_on',
    ];

    protected $casts = [
        'paid_on'            => 'date',
        'allowances_detail'  => 'array',
        'deductions_detail'  => 'array',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Staff::class, 'staff_id');
    }
}
