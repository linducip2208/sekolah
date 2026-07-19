<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AI\AiUsageLog;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiUsageController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date_from ?: now()->subDays(30)->toDateString();
        $to   = $request->date_to   ?: now()->toDateString();

        $bySchool = AiUsageLog::query()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select('school_id',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(input_tokens) as input_tokens'),
                DB::raw('SUM(output_tokens) as output_tokens'),
                DB::raw('SUM(estimated_cost) as total_cost'))
            ->groupBy('school_id')
            ->orderByDesc('total_cost')
            ->get();

        $schools = School::whereIn('id', $bySchool->pluck('school_id'))->get()->keyBy('id');

        $totals = [
            'calls'        => $bySchool->sum('calls'),
            'input_tokens' => $bySchool->sum('input_tokens'),
            'output_tokens'=> $bySchool->sum('output_tokens'),
            'total_cost'   => $bySchool->sum('total_cost'),
        ];

        return view('super-admin.ai.usage', compact('bySchool', 'schools', 'totals', 'from', 'to'));
    }
}
