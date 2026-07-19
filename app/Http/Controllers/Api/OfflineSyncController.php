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
            'records'   => 'required|array|max:500',
            'records.*.type' => 'required|string|in:attendance,mark',
            'records.*.student_id' => 'required|integer',
            'records.*.local_id' => 'nullable|string',
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
