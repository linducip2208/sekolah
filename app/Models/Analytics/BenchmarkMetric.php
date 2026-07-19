<?php

namespace App\Models\Analytics;

use App\Models\Foundation\Foundation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BenchmarkMetric extends Model
{
    protected $fillable = [
        'foundation_id', 'metric_key', 'metric_name',
        'description', 'unit', 'data_source',
        'aggregation', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function foundation(): BelongsTo
    {
        return $this->belongsTo(Foundation::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(BenchmarkResult::class);
    }
}
