<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class EnsureSchoolAccess
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        if (!$user->school_id || !$user->school?->is_active) {
            return response()->json(['message' => 'School not found or inactive.'], 403);
        }

        app()->instance('current_school', $user->school);

        return $next($request);
    }
}
