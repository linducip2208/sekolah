<?php

namespace App\Http\Controllers\Web\Admin\Phase11;

use App\Http\Controllers\Controller;
use App\Models\Analytics\StudentRiskScore;
use App\Models\Dapodik\DapodikConfig;
use App\Models\Dapodik\DapodikSyncLog;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetLoan;
use App\Models\Inventory\MaintenanceRequest;
use App\Models\Visitor\VisitorLog;
use Illuminate\View\View;

class Phase11WebController extends Controller
{
    public function dapodik(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.dapodik.dashboard', [
            'config' => DapodikConfig::firstOrCreate(
                ['school_id' => $schoolId],
                ['npsn' => '']
            ),
            'recentSyncs' => DapodikSyncLog::where('school_id', $schoolId)
                ->orderByDesc('created_at')->limit(20)->get(),
        ]);
    }

    public function visitors(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.visitors.dashboard', [
            'todayVisitors' => VisitorLog::where('school_id', $schoolId)
                ->whereDate('checked_in_at', today())
                ->orderByDesc('checked_in_at')->get(),
            'currentlyInside' => VisitorLog::where('school_id', $schoolId)
                ->whereNull('checked_out_at')->count(),
        ]);
    }

    public function inventory(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.inventory.dashboard', [
            'totalAssets' => Asset::where('school_id', $schoolId)->count(),
            'borrowed' => Asset::where('school_id', $schoolId)->where('status', 'borrowed')->count(),
            'maintenance' => Asset::where('school_id', $schoolId)->where('status', 'maintenance')->count(),
            'recentLoans' => AssetLoan::where('school_id', $schoolId)->orderByDesc('borrowed_at')->limit(20)->get(),
            'openMaintenance' => MaintenanceRequest::where('school_id', $schoolId)
                ->whereIn('status', ['reported', 'assigned', 'in_progress'])
                ->orderByDesc('created_at')->limit(20)->get(),
        ]);
    }

    public function analytics(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.analytics.dashboard', [
            'atRisk' => StudentRiskScore::where('school_id', $schoolId)
                ->whereDate('snapshot_date', today())
                ->whereIn('risk_level', ['high', 'critical'])
                ->orderByDesc('overall_risk')
                ->limit(50)->get(),
            'distribution' => StudentRiskScore::where('school_id', $schoolId)
                ->whereDate('snapshot_date', today())
                ->selectRaw('risk_level, COUNT(*) as cnt')
                ->groupBy('risk_level')->pluck('cnt', 'risk_level'),
        ]);
    }
}
