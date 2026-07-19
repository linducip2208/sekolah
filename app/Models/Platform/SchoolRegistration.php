<?php

namespace App\Models\Platform;

use App\Models\Plan;
use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolRegistration extends Model
{
    protected $fillable = [
        'school_name', 'subdomain', 'admin_name', 'admin_email', 'admin_phone',
        'address', 'plan_id', 'plan_price', 'billing_months',
        'status', 'payment_method', 'payment_reference', 'payment_proof_path',
        'paid_at', 'activated_at', 'activated_school_id', 'notes', 'rejection_reason',
    ];

    protected $casts = [
        'paid_at'        => 'datetime',
        'activated_at'   => 'datetime',
        'plan_price'     => 'integer',
        'billing_months' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function activatedSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'activated_school_id');
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'pending'   => 'gray',
            'verifying' => 'yellow',
            'paid'      => 'blue',
            'activated' => 'green',
            'rejected'  => 'red',
            default     => 'gray',
        };
    }
}
