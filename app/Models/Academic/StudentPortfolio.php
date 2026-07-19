<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPortfolio extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'title', 'description',
        'portfolio_type', 'file_path', 'thumbnail_path', 'url',
        'tags', 'is_public', 'share_token',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'tags'       => 'array',
        'is_public'  => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getShareUrlAttribute(): string
    {
        return route('portfolio.public', $this->share_token);
    }
}
