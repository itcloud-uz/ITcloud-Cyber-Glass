<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CheckTenantStatus::class,
            \App\Http\Middleware\SetLocaleMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/webhook/*',
            '/webhook/payme',
            '/webhook/click',
        ]);
        $middleware->alias([
            'webhook_source' => \App\Http\Middleware\VerifyWebhookSource::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
