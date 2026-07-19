<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementApproval extends Model
{
    use SoftDeletes;

    protected $table = 'procurement_approvals';

    protected $fillable = [
        'procurement_request_id', 'approver_id', 'step_order',
        'status', 'notes', 'decided_at',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
