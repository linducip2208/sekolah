<?php

namespace App\Policies;

use App\Models\User;

/**
 * Base policy for all tenant-scoped (SchoolModel) resources.
 * Enforces: super_admin = full access; otherwise user must belong to the same school.
 * Subclasses override for role-specific or per-action rules.
 */
abstract class SchoolOwnedPolicy
{
    protected function owns(?User $user, $model): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->school_id !== null
            && isset($model->school_id)
            && (int) $model->school_id === (int) $user->school_id;
    }

    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function view(?User $user, $model): bool
    {
        return $this->owns($user, $model);
    }

    public function create(?User $user): bool
    {
        return $user !== null && $user->school_id !== null;
    }

    public function update(?User $user, $model): bool
    {
        return $this->owns($user, $model);
    }

    public function delete(?User $user, $model): bool
    {
        return $this->owns($user, $model);
    }

    public function restore(?User $user, $model): bool
    {
        return $this->owns($user, $model);
    }

    public function forceDelete(?User $user, $model): bool
    {
        return $this->owns($user, $model);
    }
}
