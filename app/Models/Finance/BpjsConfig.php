<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;

class BpjsConfig extends SchoolModel
{
    protected $table = 'bpjs_configs';

    protected $fillable = [
        'school_id',
        'kesehatan_employee_pct', 'kesehatan_employer_pct', 'kesehatan_salary_cap',
        'jkk_pct', 'jkm_pct',
        'jht_employee_pct', 'jht_employer_pct',
        'jp_employee_pct', 'jp_employer_pct', 'jp_salary_cap',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'kesehatan_salary_cap' => 'integer',
        'jp_salary_cap' => 'integer',
    ];

    public static function forSchool(int $schoolId): self
    {
        return static::where('school_id', $schoolId)->firstOrCreate(
            ['school_id' => $schoolId],
            [
                'kesehatan_employee_pct' => 400,
                'kesehatan_employer_pct' => 400,
                'kesehatan_salary_cap'   => 1200000000,
                'jkk_pct'                => 24,
                'jkm_pct'                => 30,
                'jht_employee_pct'       => 200,
                'jht_employer_pct'       => 370,
                'jp_employee_pct'        => 100,
                'jp_employer_pct'        => 200,
                'jp_salary_cap'          => 955960000,
            ]
        );
    }
}
