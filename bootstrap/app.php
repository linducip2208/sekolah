<?php

use App\Http\Middleware\ApiTiming;
use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureSchoolAccess;
use App\Http\Middleware\RequestId;
use App\Http\Middleware\RequirePair;
use App\Http\Middleware\ResolveCustomDomain;
use App\Http\Middleware\ResolveSchool;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'school.access' => EnsureSchoolAccess::class,
            'resolve.school' => ResolveSchool::class,
            'role'         => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'   => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'  => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'subscription.active' => EnsureActiveSubscription::class,
            '2fa.enforce'         => EnforceTwoFactor::class,
        ]);

        $middleware->statefulApi();

        // Guests hitting /api/* must receive JSON 401 (never a redirect to a
        // named route). Web guests keep the admin login redirect.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }

            return route('admin.login', absolute: false);
        });

        // Global: correlation IDs + baseline security headers (API + web).
        $middleware->append([RequestId::class, SecurityHeaders::class]);

        $middleware->web(prepend: [RequirePair::class]);
        $middleware->web(append: [SetLocale::class, ResolveCustomDomain::class, ResolveSchool::class]);
        $middleware->api(append: [ApiTiming::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Mobile contract: every /api/* failure must be JSON, even when the
        // client (e.g. Flutter Dio default) omits `Accept: application/json`.
        // Never leak driver paths, SQL, or stack traces to API consumers.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $requestId = (string) $request->attributes->get('request_id', '');
            $json = fn (string $message, int $status, array $extra = []) => response()->json(
                array_merge(['message' => $message, 'request_id' => $requestId], $extra),
                $status
            );

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                    'request_id' => $requestId,
                ], 422);
            }
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return $json('Unauthenticated.', 401);
            }
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return $json('Forbidden.', 403);
            }
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = match (true) {
                    $status === 404 => 'Not found.',
                    $status === 403 => 'Forbidden.',
                    $status === 405 => 'Method not allowed.',
                    $status === 429 => 'Too many requests.',
                    default => ($e->getMessage() !== '' ? 'Request failed.' : 'Request failed.'),
                };

                return $json($message, $status);
            }

            // 500: generic in production; detail only with APP_DEBUG.
            $extra = config('app.debug') ? ['exception' => class_basename($e)] : [];

            return $json('Server error.', 500, $extra);
        });
    })->create();
