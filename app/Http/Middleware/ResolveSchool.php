<?php

namespace App\Http\Middleware;

use App\Models\School;
use Illuminate\Http\Request;

class ResolveSchool
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        $school = School::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        // Public forms can carry an explicit school slug when they are opened
        // from the platform domain (for example /kunjungan?school=smk-a).
        // Never accept a raw school id here; the slug is tenant-scoped and the
        // school must still be active.
        if (! $school && $request->filled('school')) {
            $school = School::where('subdomain', $request->string('school')->toString())
                ->where('is_active', true)
                ->first();
        }

        // Fallback: no custom subdomain (e.g. localhost) → resolve the
        // authenticated user's school so `current_school` is always bound.
        if (! $school && ! app()->bound('current_school') && auth()->check()) {
            $school = auth()->user()->school;
        }

        if ($school) {
            app()->instance('current_school', $school);
        }

        return $next($request);
    }
}
