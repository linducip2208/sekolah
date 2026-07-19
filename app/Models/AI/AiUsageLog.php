<?php

namespace App\Models\AI;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'school_id','user_id','ai_model_id','feature_key',
        'input_tokens','output_tokens','estimated_cost','latency_ms','success','error',
    ];

    protected $casts = [
        'input_tokens'   => 'integer',
        'output_tokens'  => 'integer',
        'estimated_cost' => 'decimal:6',
        'latency_ms'     => 'integer',
        'success'        => 'boolean',
    ];

    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
