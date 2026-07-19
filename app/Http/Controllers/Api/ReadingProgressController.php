<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facilities\BookReadingProgress;
use App\Models\Facilities\DigitalBookIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingProgressController extends Controller
{
    public function saveProgress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'access_token' => 'required|string',
            'current_page' => 'required|integer|min:1',
            'total_pages'  => 'nullable|integer|min:1',
        ]);

        $issue = DigitalBookIssue::where('access_token', $data['access_token'])
            ->where('is_active', true)
            ->first();

        if (!$issue) {
            return response()->json(['message' => 'Token akses tidak valid.'], 404);
        }

        $totalPages = $data['total_pages'] ?? 0;
        $progress = $totalPages > 0
            ? round(($data['current_page'] / $totalPages) * 100, 2)
            : 0;

        BookReadingProgress::updateOrCreate(
            ['digital_book_issue_id' => $issue->id],
            [
                'current_page'     => $data['current_page'],
                'total_pages'      => $totalPages ?: null,
                'progress_percent' => $progress,
                'last_read_at'     => now(),
            ]
        );

        return response()->json([
            'message'         => 'Progres membaca disimpan.',
            'current_page'    => $data['current_page'],
            'progress_percent'=> $progress,
        ]);
    }

    public function getProgress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'access_token' => 'required|string',
        ]);

        $issue = DigitalBookIssue::where('access_token', $data['access_token'])
            ->where('is_active', true)
            ->first();

        if (!$issue) {
            return response()->json(['message' => 'Token akses tidak valid.'], 404);
        }

        $progress = BookReadingProgress::where('digital_book_issue_id', $issue->id)->first();

        return response()->json([
            'current_page'    => $progress?->current_page ?? 1,
            'total_pages'     => $progress?->total_pages,
            'progress_percent'=> $progress?->progress_percent ?? 0,
            'last_read_at'    => $progress?->last_read_at?->toISOString(),
        ]);
    }
}
