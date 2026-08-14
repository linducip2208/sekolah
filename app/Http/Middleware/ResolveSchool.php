<?php

namespace App\Http\Middleware;

use App\Models\School;
use Illuminate\Http\Request;

class ResolveSchool
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $host      = $request->getHost();
        $subdomain = explode('.', $host)[0];

        $school = School::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        // Fallback: no custom subdomain (e.g. localhost) → resolve the
        // authenticated user's school so `current_school` is always bound.
        if (!$school && auth()->check()) {
            $school = auth()->user()->school;
        }

        if ($school) {
            app()->instance('current_school', $school);
        }

        return $next($request);
    }
}
