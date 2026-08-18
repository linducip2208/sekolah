<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PpdbAnalyticsController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $periodId = $request->get('period_id');

        $periods = PpdbPeriod::where('school_id', $schoolId)->orderByDesc('year')->get();

        $query = PpdbApplication::where('school_id', $schoolId);
        if ($periodId) {
            $query->where('ppdb_period_id', $periodId);
        }

        $total = (clone $query)->count();
        $funnelData = [
            'registered' => $total,
            'submitted'  => (clone $query)->whereIn('status', ['submitted', 'verified', 'accepted', 'enrolled'])->count(),
            'verified'   => (clone $query)->whereIn('status', ['verified', 'accepted', 'enrolled'])->count(),
            'accepted'   => (clone $query)->whereIn('status', ['accepted', 'enrolled'])->count(),
            'enrolled'   => (clone $query)->where('status', 'enrolled')->count(),
        ];

        $jalurBreakdown = $query->select('jalur', DB::raw('count(*) as total'), DB::raw("SUM(CASE WHEN status IN ('accepted','enrolled') THEN 1 ELSE 0 END) as accepted"))
            ->groupBy('jalur')
            ->get()
            ->map(fn ($row) => [
                'jalur'     => ucfirst($row->jalur),
                'total'     => $row->total,
                'accepted'  => $row->accepted,
                'rate'      => $row->total > 0 ? round($row->accepted / $row->total * 100, 1) : 0,
            ]);

        $distanceAnalysis = PpdbApplication::where('school_id', $schoolId)
            ->when($periodId, fn ($q) => $q->where('ppdb_period_id', $periodId))
            ->whereNotNull('distance_km')
            ->select(
                DB::raw("CASE WHEN distance_km <= 1 THEN '0-1 km' WHEN distance_km <= 3 THEN '1-3 km' WHEN distance_km <= 5 THEN '3-5 km' ELSE '5+ km' END as range_label"),
                DB::raw('count(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('accepted','enrolled') THEN 1 ELSE 0 END) as accepted")
            )
            ->groupBy('range_label')
            ->get()
            ->map(fn ($r) => [
                'range'    => $r->range_label,
                'total'    => $r->total,
                'accepted' => $r->accepted,
            ]);

        $currentPeriod = $periodId ? $periods->firstWhere('id', $periodId) : $periods->first();
        $previousPeriod = $periods->where('year', '<', $currentPeriod?->year)->first();
        $previousEnrolled = 0;
        if ($previousPeriod) {
            $previousEnrolled = PpdbApplication::where('school_id', $schoolId)
                ->where('ppdb_period_id', $previousPeriod->id)
                ->where('status', 'enrolled')
                ->count();
        }

        return view('school-admin.analytics.ppdb-analytics', [
            'periods'           => $periods,
            'periodId'          => $periodId,
            'funnelData'        => $funnelData,
            'jalurBreakdown'    => $jalurBreakdown,
            'distanceAnalysis'  => $distanceAnalysis,
            'currentPeriod'     => $currentPeriod,
            'previousEnrolled'  => $previousEnrolled,
        ]);
    }
}
