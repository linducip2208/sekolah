<?php

namespace App\Http\Controllers\Api\Visitor;

use App\Http\Controllers\Controller;
use App\Models\Visitor\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => VisitorLog::where('school_id', $request->user()->school_id)
                ->whereDate('checked_in_at', $request->input('date', today()->toDateString()))
                ->orderByDesc('checked_in_at')
                ->paginate(50),
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_name'   => 'required|string|max:200',
            'id_number'      => 'nullable|string|max:30',
            'phone'          => 'nullable|string|max:30',
            'photo_path'     => 'nullable|string|max:500',
            'purpose'        => 'required|string|max:500',
            'host_user_id'   => 'nullable|integer',
            'badge_no'       => 'nullable|string|max:20',
            'items_carried'  => 'nullable|array',
            'note'           => 'nullable|string|max:1000',
        ]);

        $isBlacklisted = false;
        if (!empty($data['id_number'])) {
            $isBlacklisted = \DB::table('visitor_blacklist')
                ->where('school_id', $request->user()->school_id)
                ->where('id_number', $data['id_number'])
                ->exists();
        }

        $log = VisitorLog::create(array_merge($data, [
            'school_id'        => $request->user()->school_id,
            'checked_in_at'    => now(),
            'logged_by'        => $request->user()->id,
            'is_blacklisted'   => $isBlacklisted,
        ]));

        if ($isBlacklisted) {
            return response()->json([
                'log'     => $log,
                'warning' => 'Pengunjung ini ada di blacklist!',
            ], 200);
        }

        if ($log->host_user_id) {
            \App\Jobs\NotifyHostVisitorArrivedJob::dispatch($log->id);
        }

        return response()->json($log, 201);
    }

    public function checkOut(Request $request, int $id): JsonResponse
    {
        $log = VisitorLog::where('school_id', $request->user()->school_id)->findOrFail($id);
        $log->update(['checked_out_at' => now()]);
        return response()->json($log);
    }
}
