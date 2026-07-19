<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends SchoolModel
{
    protected $table = 'documents';

    protected $fillable = [
        'school_id', 'document_category_id', 'title', 'description',
        'file_path', 'file_type', 'file_size', 'version', 'user_id',
        'is_published', 'published_at', 'download_count',
    ];

    protected $casts = [
        'file_size'       => 'integer',
        'version'         => 'integer',
        'is_published'    => 'boolean',
        'published_at'    => 'datetime',
        'download_count'  => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }
}
