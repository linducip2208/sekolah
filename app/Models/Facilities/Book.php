<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends SchoolModel
{
    protected $fillable = [
        'school_id', 'book_category_id', 'title', 'author', 'isbn',
        'publisher', 'publish_year', 'edition', 'total_quantity',
        'available_quantity', 'cover', 'barcode', 'description',
        'rack_location', 'is_active',
        'is_digital', 'digital_file_path', 'file_type', 'file_size',
        'page_count', 'preview_pages', 'is_downloadable',
        'download_count', 'read_count',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'total_quantity'     => 'integer',
        'available_quantity' => 'integer',
        'is_digital'         => 'boolean',
        'is_downloadable'    => 'boolean',
        'file_size'          => 'integer',
        'page_count'         => 'integer',
        'preview_pages'      => 'integer',
        'download_count'     => 'integer',
        'read_count'         => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }

    public function digitalIssues(): HasMany
    {
        return $this->hasMany(DigitalBookIssue::class);
    }
}
