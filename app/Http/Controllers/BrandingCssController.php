<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\Branding\BrandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandingCssController extends Controller
{
    public function css(Request $request, BrandingService $service, int $schoolId)
    {
        $school = School::findOrFail($schoolId);
        $version = (int) ($request->query('v') ?: 1);

        $css = Cache::remember("branding:css:{$schoolId}:{$version}", 3600, fn() => $service->generateCss($schoolId));

        return response($css, 200, [
            'Content-Type'  => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
