<?php

namespace App\Models\Analytics;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReport extends SchoolModel
{
    protected $fillable = [
        'school_id', 'report_template_id', 'user_id',
        'title', 'parameters', 'generated_at',
        'file_path', 'generation_time_ms',
    ];

    protected $casts = [
        'parameters'       => 'array',
        'generated_at'     => 'datetime',
        'generation_time_ms' => 'integer',
    ];

    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
