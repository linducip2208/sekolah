<?php

namespace App\Policies\Medical;

use App\Models\Medical\ClinicVisit;
use App\Models\User;

class ClinicVisitPolicy
{
    public function view(User $user, ClinicVisit $visit): bool
    {
        if ($user->school_id !== $visit->school_id) return false;
        if ($user->hasAnyRole(['admin', 'nurse'])) return true;

        if ($user->hasRole('parent')) {
            return \DB::table('parent_student')
                ->where('parent_id', $user->id)
                ->where('student_id', $visit->student_id)
                ->exists();
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'nurse']);
    }
}
