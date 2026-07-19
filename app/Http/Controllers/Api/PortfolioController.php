<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Academic\StudentPortfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function publicShow(string $token): View|JsonResponse
    {
        $portfolio = StudentPortfolio::where('share_token', $token)->firstOrFail();

        if ($portfolio->is_public) {
            $portfolio->load(['student.user:id,name', 'student.classSection.classRoom']);
        }

        $typeLabels = [
            'academic'    => 'Akademik',
            'achievement' => 'Prestasi',
            'project'     => 'Proyek',
            'certificate' => 'Sertifikat',
            'artwork'     => 'Karya Seni',
            'other'       => 'Lainnya',
        ];

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                => $portfolio->id,
                    'title'             => $portfolio->title,
                    'description'       => $portfolio->description,
                    'portfolio_type'    => $portfolio->portfolio_type,
                    'file_url'          => $portfolio->file_path ? asset('storage/' . $portfolio->file_path) : null,
                    'thumbnail_url'     => $portfolio->thumbnail_path ? asset('storage/' . $portfolio->thumbnail_path) : null,
                    'url'               => $portfolio->url,
                    'tags'              => $portfolio->tags,
                    'student_name'      => $portfolio->student?->user?->name,
                    'student_admission' => $portfolio->student?->admission_no,
                    'approved_at'       => $portfolio->approved_at?->toIso8601String(),
                    'created_at'        => $portfolio->created_at->toIso8601String(),
                ],
            ]);
        }

        return view('portfolio.public', compact('portfolio', 'typeLabels'));
    }
}
