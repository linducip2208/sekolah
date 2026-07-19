<?php

namespace App\Http\Middleware;

use App\Services\Branding\BrandingService;
use Closure;
use Illuminate\Http\Request;

class ResolveCustomDomain
{
    public function __construct(private BrandingService $branding) {}

    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());
        $defaultHost = strtolower(parse_url(config('app.url', ''), PHP_URL_HOST) ?? '');

        if ($host && $host !== $defaultHost) {
            $b = $this->branding->findByCustomDomain($host);
            if ($b) {
                $request->attributes->set('resolved_school_id', $b->school_id);
                app()->instance('resolved_school_id', $b->school_id);
            }
        }

        return $next($request);
    }
}
