<?php

namespace App\Policies\LessonPlan;

use App\Models\LessonPlan\LessonPlan;
use App\Models\User;

class LessonPlanPolicy
{
    public function view(User $user, LessonPlan $plan): bool
    {
        if ($user->school_id !== $plan->school_id) return false;
        return $user->id === $plan->teacher_id || $user->hasRole('admin');
    }

    public function update(User $user, LessonPlan $plan): bool
    {
        if ($user->school_id !== $plan->school_id) return false;
        if (!in_array($plan->status, ['draft', 'rejected'], true)) return false;
        return $user->id === $plan->teacher_id;
    }

    public function approve(User $user, LessonPlan $plan): bool
    {
        return $user->school_id === $plan->school_id
            && $user->hasRole('admin');
    }
}
