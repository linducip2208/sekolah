<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolDataExport extends Model
{
    protected $table = 'school_data_exports';

    protected $fillable = [
        'school_id', 'requested_by', 'format', 'status',
        'file_path', 'file_size_bytes', 'error',
        'included_tables', 'row_count',
        'started_at', 'completed_at', 'expires_at',
    ];

    protected $casts = [
        'included_tables' => 'array',
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function isReady(): bool
    {
        return $this->status === 'completed'
            && $this->file_path
            && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
