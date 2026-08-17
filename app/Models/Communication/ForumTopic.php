<?php

namespace App\Models\Communication;

use App\Models\Lms\Course;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumTopic extends SchoolModel
{
    protected $fillable = [
        'school_id', 'forum_category_id', 'course_id', 'user_id', 'title',
        'content', 'is_pinned', 'is_locked', 'view_count', 'last_reply_at',
    ];

    protected $casts = [
        'is_pinned'     => 'boolean',
        'is_locked'     => 'boolean',
        'view_count'    => 'integer',
        'last_reply_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class)->with('user:id,name')->orderBy('created_at');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ForumSubscription::class);
    }

    public function replyCount(): int
    {
        return $this->replies()->where('is_approved', true)->count();
    }
}
