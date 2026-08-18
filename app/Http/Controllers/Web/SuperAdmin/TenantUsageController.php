<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Saas\TenantUsage;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantUsageController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->month ?? now()->format('Y-m');

        $usage = TenantUsage::withoutGlobalScopes()
            ->with('school')
            ->where('month', $month)
            ->when($request->search, fn ($q) => $q->whereHas('school', fn ($s) => $s
                ->where('name', 'like', "%{$request->search}%")))
            ->orderByDesc('active_students')
            ->paginate(20)
            ->withQueryString();

        $summary = TenantUsage::withoutGlobalScopes()
            ->where('month', $month)
            ->selectRaw('
                SUM(active_students) as total_students,
                SUM(active_teachers) as total_teachers,
                SUM(total_logins) as total_logins,
                SUM(api_calls) as total_api_calls,
                SUM(storage_used_bytes) as total_storage,
                SUM(sms_sent) as total_sms,
                SUM(emails_sent) as total_emails,
                COUNT(*) as school_count
            ')
            ->first();

        $months = TenantUsage::withoutGlobalScopes()
            ->selectRaw('DISTINCT month')
            ->orderByDesc('month')
            ->limit(12)
            ->pluck('month');

        return view('super-admin.tenant-usage.index', compact('usage', 'summary', 'month', 'months'));
    }
}
