<?php

namespace App\Models\Communication;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentApproval extends Model
{
    use SoftDeletes;

    protected $table = 'document_approvals';

    protected $fillable = [
        'document_id', 'approver_id', 'status', 'notes', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
