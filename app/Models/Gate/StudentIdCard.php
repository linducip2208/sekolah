<?php

namespace App\Models\Gate;

use App\Models\SchoolModel;

class StudentIdCard extends SchoolModel
{
    protected $table = 'student_id_cards';

    protected $fillable = [
        'school_id','student_id','card_uid','qr_token','is_active','issued_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'issued_at' => 'datetime',
    ];
}
