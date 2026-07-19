<?php

namespace App\Policies\Payment;

use App\Models\Payment\PaymentTransaction;
use App\Models\User;

class PaymentTransactionPolicy
{
    public function view(User $user, PaymentTransaction $tx): bool
    {
        if ($user->school_id !== $tx->school_id) return false;
        if ($user->hasAnyRole(['admin', 'accountant'])) return true;
        return $tx->initiated_by === $user->id;
    }

    public function cancel(User $user, PaymentTransaction $tx): bool
    {
        if ($user->school_id !== $tx->school_id) return false;
        return $tx->initiated_by === $user->id || $user->hasRole('admin');
    }

    public function refund(User $user, PaymentTransaction $tx): bool
    {
        return $user->school_id === $tx->school_id && $user->hasRole('admin');
    }

    public function verifyManual(User $user, PaymentTransaction $tx): bool
    {
        return $user->school_id === $tx->school_id
            && $user->hasAnyRole(['admin', 'accountant']);
    }
}
