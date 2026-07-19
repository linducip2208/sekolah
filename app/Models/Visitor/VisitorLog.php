<?php

namespace App\Models\Visitor;

use App\Models\Academic\Staff;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorLog extends SchoolModel
{
    protected $table = 'visitor_logs';

    protected $fillable = [
        'school_id','visitor_name','id_number','phone','photo_path','purpose',
        'host_user_id','badge_no','checked_in_at','checked_out_at',
        'logged_by','items_carried','is_blacklisted','note',
        'qr_code','pre_registered','host_staff_id','vehicle_plate',
        'expected_arrival','status',
    ];

    protected $casts = [
        'checked_in_at'   => 'datetime',
        'checked_out_at'  => 'datetime',
        'expected_arrival' => 'datetime',
        'items_carried'   => 'array',
        'is_blacklisted'  => 'boolean',
        'pre_registered'  => 'boolean',
    ];

    public function hostStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'host_staff_id');
    }

    public function qrSessions(): HasMany
    {
        return $this->hasMany(VisitorQrSession::class);
    }
}
