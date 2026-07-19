<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends SchoolModel
{
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    protected $table = 'asset_loans';

    protected $fillable = [
        'school_id','asset_id','borrower_id','approved_by',
        'borrowed_at','due_at','returned_at','status','note',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'due_at'      => 'date',
        'returned_at' => 'date',
    ];
}
