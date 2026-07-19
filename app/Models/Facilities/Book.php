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
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'total_quantity'     => 'integer',
        'available_quantity' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }
}
