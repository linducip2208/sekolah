<?php

namespace App\Http\Controllers\Api\Religious;

use App\Http\Controllers\Controller;
use App\Models\Religious\HafalanProgress;
use App\Models\Religious\HafalanTarget;
use App\Models\Religious\IbadahLog;
use App\Services\Religious\ReligiousService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReligiousController extends Controller
{
    public function __construct(private ReligiousService $service) {}

    public function config(Request $request): JsonResponse
    {
        return response()->json($this->service->getOrCreateConfig($request->user()->school_id));
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enabled'              => 'nullable|boolean',
            'religion'             => 'nullable|in:islam,christian,catholic,hindu,buddha,confucian',
            'institution_type'     => 'nullable|string|max:50',
            'hijri_holidays'       => 'nullable|array',
            'use_hijri_calendar'   => 'nullable|boolean',
            'prayer_times_config'  => 'nullable|array',
        ]);
        return response()->json($this->service->updateConfig($request->user()->school_id, $data));
    }

    public function targets(Request $request): JsonResponse
    {
        return response()->json([
            'data' => HafalanTarget::where('school_id', $request->user()->school_id)
                ->orderByDesc('deadline')->get(),
        ]);
    }

    public function storeTarget(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_section_id' => 'nullable|integer',
            'name'             => 'required|string|max:200',
            'target_ranges'    => 'required|array',
            'start_date'       => 'required|date',
            'deadline'         => 'required|date|after_or_equal:start_date',
        ]);
        $data['school_id'] = $request->user()->school_id;
        return response()->json(HafalanTarget::create($data), 201);
    }

    public function recordHafalan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'       => 'required|integer',
            'hafalan_target_id'=> 'nullable|integer',
            'surah'            => 'required|string|max:50',
            'ayah_start'       => 'required|integer|min:1',
            'ayah_end'         => 'required|integer|gte:ayah_start',
            'memorized_at'     => 'nullable|date',
            'quality'          => 'nullable|in:excellent,good,fair,needs_review',
            'note'             => 'nullable|string|max:1000',
            'audio_path'       => 'nullable|array',
        ]);
        return response()->json($this->service->recordHafalan(
            $request->user()->school_id, $data['student_id'], $request->user()->id, $data,
        ), 201);
    }

    public function hafalanSummary(Request $request, int $studentId): JsonResponse
    {
        return response()->json($this->service->studentHafalanSummary(
            $request->user()->school_id, $studentId,
        ));
    }

    public function logIbadah(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'         => 'required|integer',
            'log_date'           => 'nullable|date',
            'subuh'              => 'nullable|in:done,late,missed,jamaah',
            'dzuhur'             => 'nullable|in:done,late,missed,jamaah',
            'ashar'              => 'nullable|in:done,late,missed,jamaah',
            'maghrib'            => 'nullable|in:done,late,missed,jamaah',
            'isya'               => 'nullable|in:done,late,missed,jamaah',
            'puasa_sunnah'       => 'nullable|boolean',
            'tilawah_done'       => 'nullable|boolean',
            'tilawah_ayah_count' => 'nullable|integer|min:0',
            'extra_amalan'       => 'nullable|array',
        ]);
        $studentId = $data['student_id']; unset($data['student_id']);
        return response()->json($this->service->logIbadah(
            $request->user()->school_id, $studentId, $request->user()->id, $data,
        ), 201);
    }

    public function ibadahSummary(Request $request, int $studentId): JsonResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        return response()->json($this->service->ibadahMonthSummary(
            $request->user()->school_id, $studentId, $month,
        ));
    }
}
