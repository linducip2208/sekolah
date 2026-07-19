<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SchoolSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    public function __construct(private SchoolSettingsService $settings) {}

    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user()->school);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|email',
            'phone'   => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string',
        ]);

        $school = $request->user()->school;
        $school->update($request->only(['name', 'email', 'phone', 'address']));

        return response()->json($school->fresh());
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $school = $request->user()->school;
        $path   = $request->file('logo')->store("logos/{$school->id}", 'public');
        $school->update(['logo' => $path]);

        return response()->json(['logo_url' => Storage::url($path)]);
    }

    public function settings(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        return response()->json($this->settings->get($school));
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $school   = $request->user()->school;
        $updated  = $this->settings->update($school, $request->all());
        return response()->json($updated->settings);
    }
}
