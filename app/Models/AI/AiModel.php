<?php

namespace App\Models\AI;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends SchoolModel
{
    protected $table = 'ai_models';

    protected $fillable = [
        'school_id','ai_provider_id','model_name','display_name','capability',
        'context_window','input_price_per_1k','output_price_per_1k','is_active',
    ];

    protected $casts = [
        'context_window'      => 'integer',
        'input_price_per_1k'  => 'decimal:6',
        'output_price_per_1k' => 'decimal:6',
        'is_active'           => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
