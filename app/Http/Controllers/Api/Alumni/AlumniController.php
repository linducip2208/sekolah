<?php

namespace App\Http\Controllers\Api\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni\AlumniProfile;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function publicDirectory(string $subdomain, Request $request): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();

        $alumni = AlumniProfile::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('verified', true)
            ->when($request->input('year'), fn ($q, $y) => $q->where('graduation_year', $y))
            ->when($request->input('industry'), fn ($q, $i) => $q->where('industry', $i))
            ->paginate(50);

        return response()->json($alumni);
    }

    public function profile(Request $request): JsonResponse
    {
        $profile = AlumniProfile::where('school_id', $request->user()->school_id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json($profile);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'graduation_year'             => 'required|integer|min:1950|max:2100',
            'class_of'                    => 'nullable|string|max:50',
            'current_position'            => 'nullable|string|max:200',
            'current_company'             => 'nullable|string|max:200',
            'city'                        => 'nullable|string|max:100',
            'country'                     => 'nullable|string|max:5',
            'linkedin_url'                => 'nullable|url|max:500',
            'industry'                    => 'nullable|string|max:100',
            'skills'                      => 'nullable|array',
            'willing_to_mentor'           => 'nullable|boolean',
            'willing_to_offer_internship' => 'nullable|boolean',
        ]);

        $profile = AlumniProfile::updateOrCreate(
            ['school_id' => $request->user()->school_id, 'user_id' => $request->user()->id],
            $data,
        );

        return response()->json($profile);
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $profile = AlumniProfile::where('school_id', $request->user()->school_id)->findOrFail($id);
        $profile->update(['verified' => true]);
        return response()->json($profile);
    }
}
