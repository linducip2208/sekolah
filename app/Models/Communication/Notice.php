<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends SchoolModel
{
    protected $fillable = [
        'school_id', 'created_by', 'title', 'content',
        'target_roles', 'target_class_sections',
        'publish_at', 'expire_at', 'is_published',
    ];

    protected $casts = [
        'target_roles'          => 'array',
        'target_class_sections' => 'array',
        'publish_at'            => 'datetime',
        'expire_at'             => 'datetime',
        'is_published'          => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
