<?php

namespace App\Services\Communication;

use App\Models\Communication\Notice;
use Illuminate\Database\Eloquent\Collection;

class NoticeService
{
    public function getForUser(): Collection
    {
        $user = auth()->user();
        $roles = $user->getRoleNames()->toArray();

        return Notice::where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>=', now());
            })
            ->where(function ($q) use ($roles) {
                $q->whereNull('target_roles')
                  ->orWhereJsonContains('target_roles', $roles[0] ?? 'student');
            })
            ->orderByDesc('publish_at')
            ->get();
    }
}
