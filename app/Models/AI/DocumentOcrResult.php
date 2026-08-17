<?php

namespace App\Models\AI;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentOcrResult extends SchoolModel
{
    protected $table = 'document_ocr_results';

    protected $fillable = [
        'school_id', 'user_id', 'filename', 'mime_type', 'file_path',
        'extracted_text', 'status', 'error',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
