<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = app('current_school');

        if (!$school || !$school->isSubscriptionActive()) {
            return response()->json(['message' => 'Subscription expired. Please renew your plan.'], 402);
        }

        return $next($request);
    }
}
