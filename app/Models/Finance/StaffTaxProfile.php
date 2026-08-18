<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTaxProfile extends SchoolModel
{
    protected $table = 'staff_tax_profiles';

    protected $fillable = [
        'school_id', 'staff_id', 'npwp', 'pTKP_status',
        'number_of_dependents', 'is_bpjs_active', 'is_pph21_active',
    ];

    protected $casts = [
        'is_bpjs_active'  => 'boolean',
        'is_pph21_active' => 'boolean',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Staff::class, 'staff_id');
    }

    /** PTKP values in cents (annual) */
    public static function ptkpValues(): array
    {
        return [
            1 => 585000000,   // TK/0
            2 => 630000000,   // TK/1
            3 => 675000000,   // K/0
            4 => 720000000,   // K/1
            5 => 765000000,   // K/2
            6 => 810000000,   // K/3
        ];
    }
}
