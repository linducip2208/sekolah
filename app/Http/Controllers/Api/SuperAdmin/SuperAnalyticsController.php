<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PlatformAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAnalyticsController extends Controller
{
    public function __construct(private PlatformAnalyticsService $analytics) {}

    public function revenue(Request $request): JsonResponse
    {
        $months = (int) ($request->months ?? 12);
        return response()->json($this->analytics->revenue($months));
    }

    public function growth(Request $request): JsonResponse
    {
        $months = (int) ($request->months ?? 12);
        return response()->json($this->analytics->growth($months));
    }
}
