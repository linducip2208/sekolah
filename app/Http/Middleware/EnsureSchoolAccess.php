<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class EnsureSchoolAccess
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('admin.login');
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        if (!$user->school_id || !$user->school?->is_active) {
            return $request->expectsJson()
                ? response()->json(['message' => 'School not found or inactive.'], 403)
                : abort(403, 'Sekolah tidak ditemukan atau tidak aktif.');
        }

        app()->instance('current_school', $user->school);

        return $next($request);
    }
}
