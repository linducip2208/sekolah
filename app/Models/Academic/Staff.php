<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends SchoolModel
{
    use AuditableModel;

    protected $table = 'staffs';

    protected $fillable = [
        'user_id', 'school_id', 'employee_id', 'department', 'designation', 'joining_date', 'basic_salary', 'whatsapp_phone',
    ];

    protected $casts = [
        'basic_salary' => 'integer',
        'joining_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
