<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->two_factor_enabled) {
            return $next($request);
        }

        if ($request->session()->has('2fa.passed')) {
            return $next($request);
        }

        if ($request->session()->has('2fa.pending_user_id')) {
            return $request->expectsJson()
                ? response()->json(['message' => '2FA challenge required.', 'two_factor_required' => true], 403)
                : redirect()->route('2fa.challenge');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->expectsJson()
            ? response()->json(['message' => 'Session expired. Please login again.'], 401)
            : redirect()->route('admin.login')->withErrors(['email' => 'Sesi 2FA telah berakhir. Silakan login kembali.']);
    }
}
