<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCertification extends SchoolModel
{
    protected $fillable = [
        'school_id', 'staff_id', 'certification_name', 'issuing_body',
        'certificate_number', 'issue_date', 'expiry_date',
        'file_path', 'is_primary', 'notes',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'is_primary'  => 'boolean',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
