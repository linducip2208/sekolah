<?php

namespace App\Models\Canteen;

use App\Models\Academic\Student;
use App\Models\SchoolTenantModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'student_id', 'canteen_wallet_id', 'type', 'transaction_type',
        'amount', 'balance_after', 'reference_type', 'reference_id',
        'idempotency_key', 'description', 'initiated_by', 'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::updating(function (): void {
            throw new \LogicException('Wallet ledger transactions are immutable.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Wallet ledger transactions cannot be deleted.');
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CanteenWallet::class, 'canteen_wallet_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
