<?php

use App\Http\Middleware\CheckSessionTimeout;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\EnsureFrontendApiRequestsAreStateful;
use App\Http\Middleware\EnsureUserIsNotArchived;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(
            TrustProxies::class,
            App\Http\Middleware\TrustProxies::class,
        );

        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'api/register',
        ]);

        $middleware->api(prepend: [
            EnsureFrontendApiRequestsAreStateful::class,
        ]);

        $middleware->web(append: [
            CheckSessionTimeout::class,
            EnsureUserIsNotArchived::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'active.user' => EnsureUserIsNotArchived::class,
            'verified.email' => EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
