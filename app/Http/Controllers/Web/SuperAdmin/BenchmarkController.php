<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Services\BenchmarkService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenchmarkController extends Controller
{
    public function index(Request $request): View
    {
        $foundations = Foundation::orderBy('name')->get();
        $foundationId = $request->query('foundation_id', $foundations->first()?->id);
        $period = $request->query('period', now()->format('Y-m'));

        $data = null;
        if ($foundationId) {
            $service = app(BenchmarkService::class);
            $data = $service->getFoundationDashboard((int) $foundationId, $period);
        }

        return view('super-admin.benchmark.index', compact('foundations', 'foundationId', 'period', 'data'));
    }

    public function drilldown(Request $request): \Illuminate\Http\JsonResponse
    {
        $schoolId = $request->query('school_id');
        $metricKey = $request->query('metric_key');
        $months = $request->query('months', 12);

        $service = app(BenchmarkService::class);
        $trend = $service->getHistoricalTrend((int) $schoolId, $metricKey, (int) $months);

        return response()->json($trend);
    }
}
