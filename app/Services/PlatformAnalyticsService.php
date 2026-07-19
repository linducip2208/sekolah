<?php

namespace App\Services;

use App\Models\Finance\SubscriptionTransaction;
use App\Models\School;
use App\Models\User;

class PlatformAnalyticsService
{
    public function revenue(int $months = 12): array
    {
        $monthExpr = SuperAdminService::monthExpr('created_at');
        return SubscriptionTransaction::withoutGlobalScopes()
            ->where('status', 'paid')
            ->selectRaw("{$monthExpr} as month, plan_id, SUM(amount) as total")
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month', 'plan_id')
            ->with('plan')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'plan'  => $r->plan?->name,
                'total' => (int) $r->total,
            ])
            ->values()
            ->toArray();
    }

    public function growth(int $months = 12): array
    {
        $monthExpr = SuperAdminService::monthExpr('created_at');
        $schoolGrowth = School::withoutGlobalScopes()
            ->selectRaw("{$monthExpr} as month, COUNT(*) as new_schools")
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $studentGrowth = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->selectRaw("{$monthExpr} as month, COUNT(*) as new_students")
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months_range = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $months_range->push(now()->subMonths($i)->format('Y-m'));
        }

        return $months_range->map(fn($month) => [
            'month'        => $month,
            'new_schools'  => (int) ($schoolGrowth[$month]->new_schools ?? 0),
            'new_students' => (int) ($studentGrowth[$month]->new_students ?? 0),
        ])->values()->toArray();
    }
}
