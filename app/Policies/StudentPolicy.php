<?php

namespace App\Policies;

use App\Models\Academic\Student;
use App\Models\User;

class StudentPolicy extends SchoolOwnedPolicy
{
    public function delete(?User $user, Student $student): bool
    {
        // Only admin/super_admin may delete a student record.
        return $user && ($user->hasRole(['super_admin', 'admin'])) && $this->owns($user, $student);
    }
}
