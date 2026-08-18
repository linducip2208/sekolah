<?php

namespace App\Models\PPDB;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbFormField extends SchoolModel
{
    protected $table = 'ppdb_form_fields';

    protected $fillable = [
        'school_id', 'period_id', 'field_name', 'field_type', 'field_label',
        'options', 'is_required', 'validation_rules', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'options'           => 'array',
        'validation_rules'  => 'array',
        'is_required'       => 'boolean',
        'is_active'         => 'boolean',
        'sort_order'        => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'period_id');
    }
}
