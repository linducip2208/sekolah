<?php

namespace App\Http\Controllers\Web\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiUsageDashboardController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $from = $request->date_from ?: now()->subDays(30)->toDateString();
        $to   = $request->date_to   ?: now()->toDateString();

        $base = AiUsageLog::query()
            ->where('school_id', $schoolId)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $kpis = (clone $base)->selectRaw('
            COUNT(*) as calls,
            COALESCE(SUM(input_tokens), 0) as input_tokens,
            COALESCE(SUM(output_tokens), 0) as output_tokens,
            COALESCE(SUM(estimated_cost), 0) as total_cost,
            COALESCE(AVG(latency_ms), 0) as avg_latency,
            COALESCE(SUM(CASE WHEN success THEN 0 ELSE 1 END), 0) as errors
        ')->first();

        $byFeature = (clone $base)
            ->select('feature_key',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(input_tokens) as input_tokens'),
                DB::raw('SUM(output_tokens) as output_tokens'),
                DB::raw('SUM(estimated_cost) as total_cost'))
            ->groupBy('feature_key')
            ->orderByDesc('total_cost')
            ->get();

        $byModel = (clone $base)
            ->select('ai_model_id',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(estimated_cost) as total_cost'))
            ->groupBy('ai_model_id')
            ->orderByDesc('total_cost')
            ->with('aiModel:id,model_name,display_name,ai_provider_id')
            ->get();

        $dailySeries = (clone $base)
            ->select(DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(estimated_cost) as total_cost'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $recentErrors = (clone $base)
            ->where('success', false)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('school-admin.ai.usage', compact(
            'kpis', 'byFeature', 'byModel', 'dailySeries', 'recentErrors', 'from', 'to'
        ));
    }
}
