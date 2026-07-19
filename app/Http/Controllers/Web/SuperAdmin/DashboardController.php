<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubscriptionTransaction;
use App\Models\Plan;
use App\Models\School;
use App\Services\SuperAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private SuperAdminService $service) {}

    public function index(): View
    {
        return view('super-admin.dashboard', [
            'stats' => $this->service->dashboardStats(),
        ]);
    }

    public function schools(Request $request): View
    {
        $schools = School::withoutGlobalScopes()
            ->with('plan')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'suspended', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(20);

        return view('super-admin.schools.index', compact('schools'));
    }

    public function createSchool(): View
    {
        $plans = Plan::where('is_active', true)->get();
        return view('super-admin.schools.create', compact('plans'));
    }

    public function storeSchool(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'subdomain'      => 'required|string|max:100|unique:schools,subdomain|alpha_dash',
            'email'          => 'nullable|email',
            'plan_id'        => 'nullable|integer|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
            'admin_name'     => 'sometimes|string|max:255',
            'admin_email'    => 'sometimes|email|unique:users,email',
            'admin_password' => 'sometimes|string|min:8',
        ]);

        $this->service->createSchool($validated);

        return redirect()->route('super.schools.index')->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function showSchool(School $school): View
    {
        $school->load('plan');
        $stats = $this->service->schoolStats($school);
        $plans = Plan::where('is_active', true)->get();
        return view('super-admin.schools.show', compact('school', 'stats', 'plans'));
    }

    public function suspendSchool(School $school): RedirectResponse
    {
        $this->service->suspendSchool($school);
        return back()->with('success', "Sekolah {$school->name} telah disuspend.");
    }

    public function activateSchool(School $school): RedirectResponse
    {
        $this->service->activateSchool($school);
        return back()->with('success', "Sekolah {$school->name} telah diaktifkan.");
    }

    public function extendSchool(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id'    => 'required|integer|exists:plans,id',
            'expires_at' => 'required|date',
        ]);
        $this->service->extendSubscription($school, $validated['plan_id'], $validated['expires_at']);
        return back()->with('success', 'Langganan berhasil diperpanjang.');
    }

    public function plans(): View
    {
        $plans = Plan::withCount(['schools' => fn($q) => $q->withoutGlobalScopes()])->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'slug'         => 'required|string|unique:plans,slug',
            'price'        => 'required|integer|min:0',
            'max_students' => 'sometimes|integer|min:0',
            'features_raw' => 'nullable|string',
        ]);

        $features = [];
        if (!empty($validated['features_raw'])) {
            $features = json_decode($validated['features_raw'], true) ?? [];
        }

        Plan::create([
            'name'         => $validated['name'],
            'slug'         => $validated['slug'],
            'price'        => $validated['price'],
            'max_students' => $validated['max_students'] ?? 0,
            'features'     => $features,
        ]);

        return back()->with('success', 'Plan berhasil dibuat.');
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->only(['is_active', 'price', 'name']));
        return back()->with('success', 'Plan diperbarui.');
    }

    public function subscriptions(Request $request): View
    {
        $subscriptions = SubscriptionTransaction::withoutGlobalScopes()
            ->with('plan')
            ->latest()
            ->paginate(20);
        return view('super-admin.subscriptions.index', compact('subscriptions'));
    }

    public function analytics(): View
    {
        $analytics = app(\App\Services\PlatformAnalyticsService::class);
        $monthExpr = \App\Services\SuperAdminService::monthExpr('created_at');

        $revenue = \App\Models\Finance\SubscriptionTransaction::withoutGlobalScopes()
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("$monthExpr as month, SUM(amount) as total")
            ->groupBy(\Illuminate\Support\Facades\DB::raw($monthExpr))
            ->orderBy(\Illuminate\Support\Facades\DB::raw($monthExpr))
            ->get();

        $growth = $analytics->growth(12);

        $totalRevenue = (int) \App\Models\Finance\SubscriptionTransaction::withoutGlobalScopes()->where('status', 'paid')->sum('amount');
        $totalSchools = \App\Models\School::withoutGlobalScopes()->count();
        $totalStudents = \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count();
        $totalTeachers = \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->count();

        $planDist = \App\Models\Plan::withCount(['schools' => fn ($q) => $q->withoutGlobalScopes()])->get()
            ->map(fn ($p) => ['plan' => $p->name, 'count' => $p->schools_count]);

        return view('super-admin.analytics.index', compact(
            'revenue', 'growth', 'totalRevenue', 'totalSchools', 'totalStudents', 'totalTeachers', 'planDist'
        ));
    }

    public function config(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if ($request->isMethod('PUT')) {
            \Illuminate\Support\Facades\Cache::forever('system_config', array_merge(
                \Illuminate\Support\Facades\Cache::get('system_config', []),
                $request->except('_token', '_method')
            ));
            return back()->with('success', 'Konfigurasi disimpan.');
        }

        $config = \Illuminate\Support\Facades\Cache::get('system_config', [
            'app_name'              => 'eSchool SaaS',
            'trial_days'            => 14,
            'support_email'         => 'support@whitelabel.co.id',
            'maintenance_mode'      => false,
            'allow_school_register' => true,
        ]);

        return view('super-admin.config.index', compact('config'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('super.login');
    }
}
