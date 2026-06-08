<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            // MAC — Matrix Access Control
            // CheckPortalAccess  = block-gate (vào portal được không?)
            // CheckFeatureAccess = feature-gate (dùng tính năng cụ thể được không?)
            'portal.access'     => \App\Http\Middleware\CheckFeatureAccess::class,
            'secretary.context' => \App\Http\Middleware\EnsureSecretaryContext::class,
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
