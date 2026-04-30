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
    $middleware->alias([
        'student'  => \App\Http\Middleware\CheckStudent::class,
        'admin'    => \App\Http\Middleware\CheckAdmin::class,
        'archived' => \App\Http\Middleware\CheckArchivedUser::class,
    ]);

    $middleware->validateCsrfTokens(except: [
        'webhook/resend',
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
