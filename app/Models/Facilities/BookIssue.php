<?php

namespace App\Models\Facilities;

use App\Models\Scopes\SchoolScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookIssue extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new SchoolScope());
        static::creating(function (self $model) {
            if (empty($model->school_id) && auth()->check()) {
                $model->school_id = auth()->user()->school_id;
            }
        });
    }

    protected $fillable = [
        'school_id', 'book_id', 'issued_to', 'issued_by', 'returned_to',
        'issue_date', 'due_date', 'return_date', 'status',
        'fine_amount', 'fine_paid', 'note',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'return_date' => 'date',
        'fine_paid'   => 'boolean',
        'fine_amount' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }
}
