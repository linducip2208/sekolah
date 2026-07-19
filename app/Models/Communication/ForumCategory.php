<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumCategory extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class)->orderByDesc('is_pinned')->orderByDesc('last_reply_at');
    }
}
