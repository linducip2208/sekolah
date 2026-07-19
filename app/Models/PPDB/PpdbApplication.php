<?php

namespace App\Models\PPDB;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbApplication extends SchoolModel
{
    protected $table = 'ppdb_applications';

    public const STATUSES = ['draft','submitted','verified','accepted','rejected','enrolled','withdrew'];
    public const JALUR    = ['zonasi','prestasi','afirmasi','undian','reguler'];

    protected $fillable = [
        'school_id','ppdb_period_id','registration_no','jalur',
        'student_name','nisn','date_of_birth','gender','address','district','city',
        'home_lat','home_lng','distance_km','previous_school',
        'parent_name','parent_phone','parent_email',
        'documents','achievements','average_score','ranking_score','rank_position',
        'status','reviewer_id','reviewer_note','form_payment_id',
        'submitted_at','verified_at','accepted_at',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'home_lat'        => 'decimal:7',
        'home_lng'        => 'decimal:7',
        'distance_km'     => 'decimal:3',
        'documents'       => 'array',
        'achievements'    => 'array',
        'average_score'   => 'decimal:2',
        'ranking_score'   => 'decimal:3',
        'submitted_at'    => 'datetime',
        'verified_at'     => 'datetime',
        'accepted_at'     => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'ppdb_period_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
