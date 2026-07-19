<?php

namespace App\Http\Controllers\Web\Admin\Foundation;

use App\Http\Controllers\Controller;
use App\Services\BenchmarkService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoundationBenchmarkController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('period', now()->format('Y-m'));
        $schoolId = auth()->user()->school_id;

        $service = app(BenchmarkService::class);
        $data = $service->getSchoolSelfComparison($schoolId, $period);

        return view('school-admin.foundation.benchmark', compact('data', 'period'));
    }

    public function trend(Request $request): \Illuminate\Http\JsonResponse
    {
        $schoolId = auth()->user()->school_id;
        $metricKey = $request->query('metric_key');
        $months = $request->query('months', 12);

        $service = app(BenchmarkService::class);
        $trend = $service->getHistoricalTrend($schoolId, $metricKey, (int) $months);

        return response()->json($trend);
    }
}
