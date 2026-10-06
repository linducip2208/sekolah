<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Correlation IDs for production diagnostics. Reuses an inbound
 * X-Request-ID when valid, otherwise mints one, and always echoes it
 * back so clients can reference it in bug reports.
 */
class RequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->header('X-Request-ID', '');
        $id = preg_match('/^[A-Za-z0-9\-_]{8,64}$/', $incoming) === 1
            ? $incoming
            : (string) Str::uuid();

        $request->attributes->set('request_id', $id);
        \Illuminate\Support\Facades\Log::shareContext(['request_id' => $id]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-ID', $id);

        return $response;
    }
}
