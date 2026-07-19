<?php

use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureSchoolAccess;
use App\Http\Middleware\RequirePair;
use App\Http\Middleware\ResolveCustomDomain;
use App\Http\Middleware\ResolveSchool;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

        $middleware->web(prepend: [RequirePair::class]);
        $middleware->web(append: [SetLocale::class, ResolveCustomDomain::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
