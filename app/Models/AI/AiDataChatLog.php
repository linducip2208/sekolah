<?php

namespace App\Models\AI;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDataChatLog extends SchoolModel
{
    protected $table = 'ai_data_chat_logs';

    protected $fillable = [
        'school_id', 'user_id', 'question', 'metric_key', 'result', 'answer', 'used_ai',
    ];

    protected $casts = [
        'result'  => 'array',
        'used_ai' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
