<?php

namespace App\Models\Analytics;

use App\Models\School;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkResult extends SchoolModel
{
    protected $fillable = [
        'school_id', 'benchmark_metric_id', 'period',
        'value', 'rank', 'percentile', 'calculated_at',
    ];

    protected $casts = [
        'value'         => 'decimal:4',
        'percentile'    => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function benchmarkMetric(): BelongsTo
    {
        return $this->belongsTo(BenchmarkMetric::class);
    }
}
