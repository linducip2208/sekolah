<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReadingProgress extends Model
{
    protected $table = 'book_reading_progress';

    protected $fillable = [
        'digital_book_issue_id', 'current_page', 'total_pages',
        'last_read_at', 'progress_percent',
    ];

    protected $casts = [
        'current_page'    => 'integer',
        'total_pages'     => 'integer',
        'last_read_at'    => 'datetime',
        'progress_percent'=> 'decimal:2',
    ];

    public function digitalBookIssue(): BelongsTo
    {
        return $this->belongsTo(DigitalBookIssue::class);
    }
}
