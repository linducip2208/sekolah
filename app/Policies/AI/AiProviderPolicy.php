<?php

namespace App\Policies\AI;

use App\Models\AI\AiProvider;
use App\Models\User;

class AiProviderPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, AiProvider $provider): bool
    {
        return $user->school_id === $provider->school_id && $user->hasRole('admin');
    }
}
