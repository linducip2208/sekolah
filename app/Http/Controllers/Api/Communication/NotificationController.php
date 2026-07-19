<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = NotificationLog::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);
        return response()->json($notifications);
    }

    public function markRead(int $id): JsonResponse
    {
        NotificationLog::where('user_id', auth()->id())->findOrFail($id)->update(['is_read' => true]);
        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(): JsonResponse
    {
        NotificationLog::where('user_id', auth()->id())->update(['is_read' => true]);
        return response()->json(['message' => 'All marked as read.']);
    }

    public function unreadCount(): JsonResponse
    {
        $count = NotificationLog::where('user_id', auth()->id())->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }
}
