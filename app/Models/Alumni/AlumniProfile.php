<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniProfile extends SchoolModel
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $table = 'alumni_profiles';

    protected $fillable = [
        'school_id','user_id','graduation_year','class_of',
        'current_position','current_company','city','country','linkedin_url',
        'industry','skills','willing_to_mentor','willing_to_offer_internship','verified',
    ];

    protected $casts = [
        'graduation_year'             => 'integer',
        'skills'                      => 'array',
        'willing_to_mentor'           => 'boolean',
        'willing_to_offer_internship' => 'boolean',
        'verified'                    => 'boolean',
    ];
}
