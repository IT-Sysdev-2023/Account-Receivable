<?php

use App\Http\Middleware\CheckUserPermission;
use App\Http\Middleware\HandleInertiaRequests;
use App\Providers\BroadcastServiceProvider;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.permission' => CheckUserPermission::class,
            'switchBuDatabase' => App\Http\Middleware\SwitchBuDatabase::class,
            // 'perBuPrefix' => App\Http\Middleware\PerBuPrefix::class,
        ]);
        $middleware->api([
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            App\Http\Middleware\SwitchBuDatabase::class,
            // App\Http\Middleware\PerBuPrefix::class,

        ]);
        $middleware->web(append: [
            HandleInertiaRequests::class,
            App\Http\Middleware\SwitchBuDatabase::class,
            // App\Http\Middleware\PerBuPrefix::class,

        ]);
        $middleware->validateCsrfTokens(except: [
            'bilarbreeder/broadcasting/*',
            'bilarbreeder/invoice-report',
            'invoice-report',
            'api/insertcustomerledger',
            'bilar_breeder_ar/api/insertpayment',
            'api/*', // Or wildcard for all API routes
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            return redirect()->guest(route('session.expired'));
        });
        $exceptions->renderable(function (NotFoundHttpException $e) {
            return Inertia::render('PageNotFound');
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e) {
            return Inertia::render('Forbidden');
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) {
                return Inertia::render('Forbidden');
            }
        });
    })
    ->withProviders([
        BroadcastServiceProvider::class,
    ])->create();
