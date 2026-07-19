<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\School;
use App\Models\Academic\Student;
use App\Models\Finance\SubscriptionTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SuperAdminService
{
    public static function monthExpr(string $column = 'created_at'): string
    {
        $driver = DB::connection()->getDriverName();
        return match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql'  => "to_char({$column}, 'YYYY-MM')",
            default  => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    public function dashboardStats(): array
    {
        Cache::forget('super_dashboard_stats');
        return $this->computeDashboardStats();
    }

    private function computeDashboardStats(): array
    {
        $totalSchools     = School::withoutGlobalScopes()->count();
        $activeSchools    = School::withoutGlobalScopes()->where('is_active', true)->count();
        $suspendedSchools = $totalSchools - $activeSchools;
        $totalStudents    = User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'student'))->count();
        $totalTeachers    = User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('name', 'teacher'))->count();

        $revenueThisMonth = SubscriptionTransaction::withoutGlobalScopes()
            ->where('status', 'paid')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $planDistribution = Plan::withCount(['schools' => fn($q) => $q->withoutGlobalScopes()])
            ->get()
            ->map(function ($plan) use ($totalSchools) {
                return [
                    'plan'       => (string) $plan->name,
                    'count'      => (int) $plan->schools_count,
                    'percentage' => $totalSchools > 0
                        ? round($plan->schools_count / $totalSchools * 100, 1)
                        : 0,
                ];
            })
            ->values()
            ->all();

        $expiringSoon = School::withoutGlobalScopes()
            ->with('plan')
            ->where('is_active', true)
            ->whereBetween('plan_expires_at', [now(), now()->addDays(30)])
            ->orderBy('plan_expires_at')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'school'     => (string) $s->name,
                'expires_at' => $s->plan_expires_at?->toDateString(),
                'plan'       => (string) ($s->plan?->name ?? '-'),
            ])
            ->values()
            ->all();

        $monthExpr = self::monthExpr('created_at');
        $monthlyRevenue = SubscriptionTransaction::withoutGlobalScopes()
            ->where('status', 'paid')
            ->selectRaw("{$monthExpr} as month, SUM(amount) as amount_cents")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => (string) $r->month, 'amount_cents' => (int) $r->amount_cents])
            ->values()
            ->all();

        $newSchoolsThisMonth = School::withoutGlobalScopes()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            'overview' => [
                'total_schools'                  => $totalSchools,
                'active_schools'                 => $activeSchools,
                'suspended_schools'              => $suspendedSchools,
                'total_students'                 => $totalStudents,
                'total_teachers'                 => $totalTeachers,
                'total_revenue_this_month_cents' => (int) $revenueThisMonth,
            ],
            'plan_distribution'          => $planDistribution,
            'subscriptions_expiring_soon' => $expiringSoon,
            'monthly_revenue'            => $monthlyRevenue,
            'new_schools_this_month'     => $newSchoolsThisMonth,
        ];
    }

    public function createSchool(array $data): School
    {
        return DB::transaction(function () use ($data) {
            $school = School::create([
                'name'      => $data['name'],
                'subdomain' => $data['subdomain'],
                'email'     => $data['email'] ?? null,
                'phone'     => $data['phone'] ?? null,
                'address'   => $data['address'] ?? null,
                'plan_id'   => $data['plan_id'] ?? null,
                'plan_expires_at' => isset($data['plan_expires_at']) ? $data['plan_expires_at'] : null,
                'is_active' => true,
                'settings'  => [],
            ]);

            if (!empty($data['admin_name']) && !empty($data['admin_email'])) {
                $admin = User::create([
                    'name'      => $data['admin_name'],
                    'email'     => $data['admin_email'],
                    'password'  => bcrypt($data['admin_password'] ?? 'Password123!'),
                    'school_id' => $school->id,
                    'is_active' => true,
                ]);
                $admin->assignRole('admin');
            }

            return $school;
        });
    }

    public function suspendSchool(School $school): School
    {
        $school->update(['is_active' => false]);
        User::where('school_id', $school->id)->update(['is_active' => false]);
        return $school->fresh();
    }

    public function activateSchool(School $school): School
    {
        $school->update(['is_active' => true]);
        User::where('school_id', $school->id)->update(['is_active' => true]);
        return $school->fresh();
    }

    public function schoolStats(School $school): array
    {
        return [
            'school_id' => $school->id,
            'name'      => $school->name,
            'students'  => User::where('school_id', $school->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'student'))->count(),
            'teachers'  => User::where('school_id', $school->id)
                ->whereHas('roles', fn($q) => $q->where('name', 'teacher'))->count(),
            'plan'      => $school->plan?->name,
            'plan_expires_at' => $school->plan_expires_at?->toDateString(),
            'is_active' => $school->is_active,
        ];
    }

    public function extendSubscription(School $school, int $planId, string $expiresAt): School
    {
        $school->update([
            'plan_id'         => $planId,
            'plan_expires_at' => $expiresAt,
            'is_active'       => true,
        ]);
        return $school->fresh('plan');
    }

    public function recordSubscription(array $data): SubscriptionTransaction
    {
        $school = School::findOrFail($data['school_id']);
        $tx = SubscriptionTransaction::create([
            'school_id'      => $school->id,
            'plan_id'        => $data['plan_id'],
            'amount'         => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'reference'      => $data['reference'] ?? null,
            'status'         => 'paid',
            'period_from'    => $data['period_from'],
            'period_to'      => $data['period_to'],
        ]);

        $school->update([
            'plan_id'         => $data['plan_id'],
            'plan_expires_at' => $data['period_to'],
        ]);

        return $tx;
    }
}
