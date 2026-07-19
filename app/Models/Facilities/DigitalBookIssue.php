<?php

namespace App\Models\Facilities;

use App\Models\Scopes\SchoolScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DigitalBookIssue extends Model
{
    use SoftDeletes;

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
        'school_id', 'book_id', 'student_id', 'staff_id',
        'issued_at', 'access_expires_at', 'access_token', 'is_active',
    ];

    protected $casts = [
        'issued_at'         => 'datetime',
        'access_expires_at' => 'datetime',
        'is_active'         => 'boolean',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function readingProgress(): HasOne
    {
        return $this->hasOne(BookReadingProgress::class);
    }
}
