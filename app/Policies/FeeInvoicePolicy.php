<?php

namespace App\Policies;

use App\Models\Finance\FeeInvoice;
use App\Models\User;

class FeeInvoicePolicy extends SchoolOwnedPolicy
{
    public function delete(?User $user, FeeInvoice $invoice): bool
    {
        // Only admin/super_admin/accountant may delete an invoice.
        return $user && $user->hasRole(['super_admin', 'admin', 'accountant']) && $this->owns($user, $invoice);
    }
}
