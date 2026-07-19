<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni\AlumniProfile;
use App\Models\Alumni\TracerQuestion;
use App\Models\Alumni\TracerResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TracerController extends Controller
{
    public function showForm(Request $request): JsonResponse
    {
        $alumni = AlumniProfile::where('id', $request->alumni_id)->first();
        if (!$alumni) {
            return response()->json(['message' => 'Alumni tidak ditemukan.'], 404);
        }

        $questions = TracerQuestion::where('school_id', $alumni->school_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'alumni'    => [
                'id'   => $alumni->id,
                'name' => $alumni->user?->name,
                'year' => $alumni->graduation_year,
            ],
            'questions' => $questions->map(fn($q) => [
                'id'            => $q->id,
                'question_text' => $q->question_text,
                'question_type' => $q->question_type,
                'options'       => $q->options,
            ]),
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alumni_id'       => 'required|exists:alumni_profiles,id',
            'status'          => 'required|in:kerja,kuliah,wirausaha,menganggur,lainnya',
            'company_name'    => 'nullable|string|max:200',
            'position'        => 'nullable|string|max:200',
            'salary_range'    => 'nullable|string|max:50',
            'is_relevant'     => 'nullable|boolean',
            'feedback'        => 'nullable|string',
            'answers'         => 'nullable|array',
        ]);

        $alumni = AlumniProfile::findOrFail($data['alumni_id']);

        TracerResponse::create([
            'school_id'        => $alumni->school_id,
            'alumni_profile_id'=> $alumni->id,
            'graduation_year'  => $alumni->graduation_year,
            'status'           => $data['status'],
            'company_name'     => $data['company_name'] ?? null,
            'position'         => $data['position'] ?? null,
            'salary_range'     => $data['salary_range'] ?? null,
            'is_relevant'      => $data['is_relevant'] ?? null,
            'feedback'         => $data['feedback'] ?? null,
            'answers'          => $data['answers'] ?? null,
            'submitted_at'     => now(),
        ]);

        return response()->json(['message' => 'Terima kasih! Data tracer study berhasil disimpan.']);
    }
}
