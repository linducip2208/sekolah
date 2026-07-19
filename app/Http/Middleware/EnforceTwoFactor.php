<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceTwoFactor
{
    public function handle(Request $request, Closure $next, ?string $roles = null)
    {
        return $next($request);
    }
}
