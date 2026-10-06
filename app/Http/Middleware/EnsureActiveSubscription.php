<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = app()->bound('current_school') ? app('current_school') : ($request->user()?->school);

        // Only enforce for schools with a finite subscription (plan_expires_at set).
        // Free / unlimited schools (no expiry) are never blocked.
        if ($school && $school->plan_expires_at && !$school->isSubscriptionActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription expired. Please renew your plan.'], 402);
            }

            return response()->view('errors.subscription-expired', ['school' => $school], 402);
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Grace warning: expired tapi masih dalam 7 hari toleransi -> beri header agar UI bisa tampilkan banner.
        if ($school && $school->plan_expires_at && now()->gte($school->plan_expires_at) && $school->isSubscriptionActive()) {
            $response->headers->set('X-Subscription-Warning', 'grace-period');
        }

        return $response;
    }
}
