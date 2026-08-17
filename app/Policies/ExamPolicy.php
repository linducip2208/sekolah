<?php

namespace App\Policies;

use App\Models\Academic\Exam;
use App\Models\User;

class ExamPolicy extends SchoolOwnedPolicy
{
    public function delete(?User $user, Exam $exam): bool
    {
        return $user && $user->hasRole(['super_admin', 'admin', 'teacher']) && $this->owns($user, $exam);
    }
}
