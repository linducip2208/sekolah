<?php

namespace App\Policies\Counseling;

use App\Models\Counseling\CounselingSession;
use App\Models\User;

class CounselingSessionPolicy
{
    /**
     * Counseling notes are PRIVATE — only the assigned counselor + admin can view notes.
     * Students/parents can see scheduled time only via separate response shape.
     */
    public function viewNotes(User $user, CounselingSession $session): bool
    {
        if ($user->school_id !== $session->school_id) return false;
        return $user->id === $session->counselor_id || $user->hasRole('admin');
    }

    public function viewSchedule(User $user, CounselingSession $session): bool
    {
        if ($user->school_id !== $session->school_id) return false;
        if ($user->hasAnyRole(['admin', 'counselor'])) return true;

        if ($user->hasRole('parent')) {
            return \DB::table('parent_student')
                ->where('parent_id', $user->id)
                ->where('student_id', $session->student_id)
                ->exists();
        }

        return false;
    }
}
