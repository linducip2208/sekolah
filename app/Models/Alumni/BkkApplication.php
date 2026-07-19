<?php

namespace App\Models\Alumni;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BkkApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bkk_applications';

    protected $fillable = [
        'student_id', 'job_listing_id', 'bkk_partner_id',
        'application_date', 'status', 'interview_date', 'notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'interview_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(BkkPartner::class, 'bkk_partner_id');
    }
}
