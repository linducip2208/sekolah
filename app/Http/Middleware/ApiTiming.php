<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Development-safe API timing instrumentation.
 *
 * Emits one debug log line per API request:
 *   GET /api/v1/dashboard/student 200 342ms
 *
 * Never logs Authorization headers, tokens, passwords, OTP codes, or
 * request bodies. Only active when APP_DEBUG=true so production
 * releases stay quiet.
 */
class ApiTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        if (config('app.debug')) {
            $ms = (int) ((microtime(true) - $start) * 1000);
            Log::debug('api.timing', [
                'method' => $request->getMethod(),
                'path' => '/'.ltrim($request->path(), '/'),
                'status' => $response->getStatusCode(),
                'ms' => $ms,
            ]);
        }

        $response->headers->set('X-Response-Time-Ms', (string) (int) ((microtime(true) - $start) * 1000));

        return $response;
    }
}
