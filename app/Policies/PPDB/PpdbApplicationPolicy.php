<?php

namespace App\Policies\PPDB;

use App\Models\PPDB\PpdbApplication;
use App\Models\User;

class PpdbApplicationPolicy
{
    public function view(User $user, PpdbApplication $app): bool
    {
        if ($user->school_id !== $app->school_id) return false;
        if ($user->hasAnyRole(['admin', 'receptionist'])) return true;
        return $user->email === $app->parent_email;
    }

    public function update(User $user, PpdbApplication $app): bool
    {
        if ($user->school_id !== $app->school_id) return false;
        if ($app->status !== 'draft') return false;
        return $user->email === $app->parent_email;
    }

    public function review(User $user, PpdbApplication $app): bool
    {
        return $user->school_id === $app->school_id
            && $user->hasAnyRole(['admin', 'receptionist']);
    }
}
