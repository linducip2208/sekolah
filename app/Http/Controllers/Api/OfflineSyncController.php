<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OfflineSyncService;
use Illuminate\Http\Request;

class OfflineSyncController extends Controller
{
    public function batch(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'records'   => 'required|array|max:200',
            'records.*.type' => 'required|string|in:attendance,mark',
            'records.*.student_id' => 'required|integer',
            'records.*.local_id' => 'nullable|string|max:100',
            'records.*.class_section_id' => 'nullable|integer',
            'records.*.date' => 'nullable|date_format:Y-m-d',
            'records.*.status' => 'nullable|string|max:20',
            'records.*.subject_id' => 'nullable|integer',
            'records.*.exam_id' => 'nullable|integer',
            'records.*.semester_id' => 'nullable|integer',
            'records.*.obtained_marks' => 'nullable|numeric|min:0',
            'records.*.total_marks' => 'nullable|numeric|min:1',
        ]);

        $service = app(OfflineSyncService::class);
        $result = $service->processBatch($request->input('records'));

        return response()->json([
            'success'   => true,
            'processed' => $result['processed'],
            'failed'    => $result['failed'],
            'total'     => $result['total'],
            'results'   => $result['results'],
        ], $result['failed'] > 0 ? 207 : 200);
    }
}
