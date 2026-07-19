<?php

namespace App\Http\Controllers\Web\Admin\Phase9;

use App\Http\Controllers\Controller;
use App\Models\AI\AiProvider;
use App\Models\Canteen\CanteenMenuItem;
use App\Models\Canteen\CanteenOrder;
use App\Models\LessonPlan\LessonPlan;
use App\Models\LiveClass\LiveClassSession;
use App\Models\Religious\HafalanProgress;
use App\Models\Religious\IbadahLog;
use App\Models\Religious\ReligiousModeConfig;
use Illuminate\View\View;

class Phase9WebController extends Controller
{
    public function lessonPlan(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.lesson-plan.dashboard', [
            'plans' => LessonPlan::where('school_id', $schoolId)
                ->orderByDesc('lesson_date')->limit(50)->get(),
            'stats' => [
                'submitted' => LessonPlan::where('school_id', $schoolId)->where('status', 'submitted')->count(),
                'approved'  => LessonPlan::where('school_id', $schoolId)->where('status', 'approved')->count(),
                'completed' => LessonPlan::where('school_id', $schoolId)->where('actually_executed', true)->count(),
            ],
        ]);
    }

    public function canteen(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.canteen.dashboard', [
            'menu' => CanteenMenuItem::where('school_id', $schoolId)->orderBy('name')->get(),
            'todayOrders' => CanteenOrder::where('school_id', $schoolId)
                ->whereDate('created_at', today())->orderByDesc('id')->get(),
            'todayRevenue' => CanteenOrder::where('school_id', $schoolId)
                ->whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')->sum('total'),
        ]);
    }

    public function religious(): View
    {
        $schoolId = auth()->user()->school_id;
        $config = ReligiousModeConfig::firstOrCreate(['school_id' => $schoolId]);

        return view('school-admin.religious.dashboard', [
            'config' => $config,
            'recentHafalan' => HafalanProgress::where('school_id', $schoolId)
                ->orderByDesc('memorized_at')->limit(20)->get(),
            'todayIbadah' => IbadahLog::where('school_id', $schoolId)
                ->whereDate('log_date', today())->count(),
        ]);
    }

    public function ai(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.ai.dashboard', [
            'providers' => AiProvider::where('school_id', $schoolId)->get(),
            'usage30d' => \App\Models\AI\AiUsageLog::where('school_id', $schoolId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('feature_key, COUNT(*) as cnt, SUM(input_tokens) as in_tok, SUM(output_tokens) as out_tok, SUM(estimated_cost) as cost')
                ->groupBy('feature_key')->get(),
        ]);
    }

    public function liveClass(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.live-class.dashboard', [
            'upcoming' => LiveClassSession::where('school_id', $schoolId)
                ->where('scheduled_start', '>=', now())
                ->whereIn('status', ['scheduled', 'live'])
                ->orderBy('scheduled_start')->get(),
        ]);
    }
}
