<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends SchoolModel
{
    protected $fillable = ['school_id', 'user_one', 'user_two', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
